// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Import dialog for Gradebook XP JSON exports.
 *
 * @module    gradereport_gradebook_xp/components/ImportCompetencies
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gradebook_xp/hooks/useCompetencyStore';
import {useStrings} from 'gradereport_gradebook_xp/hooks/useStrings';
import ApiService from 'gradereport_gradebook_xp/api_service';

const normalize = value => String(value || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();

const activityKey = activity => [
    normalize(activity.module),
    normalize(activity.section_name),
    normalize(activity.name)
].join('\u0000');

const validateExport = data => {
    if (!data || !Array.isArray(data.competencies) || !Array.isArray(data.relations) ||
            !Array.isArray(data.connections) || !Array.isArray(data.activities)) {
        throw new Error('invalid');
    }
    if (data.competencies.some(item => !item || !Number.isInteger(Number(item.id)) || !normalize(item.name))) {
        throw new Error('invalid');
    }
    return data;
};

/**
 * Build a non-mutating import preview.
 *
 * @param {Object} data Parsed export data
 * @param {Array} currentCompetencies Competencies in the destination course
 * @param {Array} currentActivities Grade items in the destination course
 * @returns {Object} Import plan
 */
export const buildImportPlan = (data, currentCompetencies, currentActivities) => {
    validateExport(data);

    const existingByName = new Map();
    currentCompetencies.forEach(item => existingByName.set(normalize(item.name), item));

    const destinationByKey = new Map();
    currentActivities.forEach(item => {
        const key = activityKey(item);
        destinationByKey.set(key, [...(destinationByKey.get(key) || []), item]);
    });

    const exportedActivities = new Map(data.activities.map(item => [Number(item.id), item]));
    const competencies = data.competencies.map(item => ({
        source: item,
        existing: existingByName.get(normalize(item.name)) || null
    }));
    const connectionPlans = data.connections.map(connection => {
        const sourceActivityId = Number(connection.gradeitemid || connection.activityid || 0);
        const sourceActivity = exportedActivities.get(sourceActivityId);
        const matches = sourceActivity ? (destinationByKey.get(activityKey(sourceActivity)) || []) : [];
        return {
            source: connection,
            sourceActivity,
            match: matches.length === 1 ? matches[0] : null,
            status: matches.length === 1 ? 'matched' : (matches.length > 1 ? 'ambiguous' : 'missing')
        };
    });

    return {
        data,
        competencies,
        connectionPlans,
        newCompetencies: competencies.filter(item => !item.existing).length,
        existingCompetencies: competencies.filter(item => item.existing).length,
        matchedConnections: connectionPlans.filter(item => item.status === 'matched').length,
        ambiguousConnections: connectionPlans.filter(item => item.status === 'ambiguous').length,
        missingConnections: connectionPlans.filter(item => item.status === 'missing').length
    };
};

/**
 * JSON import button and preview dialog.
 *
 * @returns {Object} React element
 */
export const ImportCompetencies = () => {
    const {createElement, useRef, useState} = window.React;
    const {courseid, competencies, activities, connections, relations} = useCompetencyStore();
    const {str} = useStrings();
    const inputRef = useRef(null);
    const [plan, setPlan] = useState(null);
    const [error, setError] = useState('');
    const [importing, setImporting] = useState(false);
    const [result, setResult] = useState(null);

    const close = () => {
        if (importing) {
            return;
        }
        if (result) {
            window.location.reload();
            return;
        }
        setPlan(null);
        setError('');
        setResult(null);
        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };

    const selectFile = async event => {
        setError('');
        setResult(null);
        const file = event.target.files?.[0];
        if (!file) {
            return;
        }
        try {
            const data = validateExport(JSON.parse(await file.text()));
            setPlan(buildImportPlan(data, competencies, activities));
        } catch (exception) {
            setPlan(null);
            setError(str('importinvalidfile'));
        }
    };

    const runImport = async() => {
        setImporting(true);
        setError('');
        const idMap = new Map();
        let createdCompetencies = 0;
        let createdRelations = 0;
        let createdConnections = 0;
        let skippedConnections = plan.ambiguousConnections + plan.missingConnections;
        const failures = [];

        try {
            for (const item of plan.competencies) {
                if (item.existing) {
                    idMap.set(Number(item.source.id), Number(item.existing.id));
                    continue;
                }
                const maximum = Math.max(1, Number(item.source.maxcomlvl) || 1);
                const requestedTarget = Number(item.source.targetcomlvl);
                const target = requestedTarget >= 1 && requestedTarget <= maximum ? requestedTarget : maximum;
                const created = await ApiService.createCompetency({
                    courseid: Number(courseid),
                    name: String(item.source.name).trim(),
                    description: String(item.source.description || ''),
                    maxcomlvl: maximum,
                    targetcomlvl: target,
                    islevelsummed: Number(item.source.islevelsummed) === 0 ? 0 : 1
                });
                idMap.set(Number(item.source.id), Number(created.id));
                createdCompetencies++;
            }

            const existingRelations = new Set(relations.map(item => `${item.parentid}:${item.childid}`));
            for (const relation of plan.data.relations) {
                const parentid = idMap.get(Number(relation.parentid));
                const childid = idMap.get(Number(relation.childid));
                const key = `${parentid}:${childid}`;
                if (!parentid || !childid || parentid === childid || existingRelations.has(key)) {
                    continue;
                }
                await ApiService.createRelation({parentid, childid});
                existingRelations.add(key);
                createdRelations++;
            }

            const existingConnections = new Set(connections.map(item => `${item.competencyid}:${item.gradeitemid}`));
            for (const item of plan.connectionPlans.filter(connection => connection.status === 'matched')) {
                const competencyid = idMap.get(Number(item.source.competencyid));
                const gradeitemid = Number(item.match.id);
                const key = `${competencyid}:${gradeitemid}`;
                if (!competencyid || existingConnections.has(key)) {
                    skippedConnections++;
                    continue;
                }
                try {
                    await ApiService.createConnection({
                        competencyid,
                        gradeitemid,
                        level: Math.max(1, Number(item.source.level) || 1)
                    });
                    existingConnections.add(key);
                    createdConnections++;
                } catch (exception) {
                    failures.push(item.sourceActivity?.name || String(item.source.activityid || item.source.gradeitemid));
                }
            }

            setResult({createdCompetencies, createdRelations, createdConnections, skippedConnections, failures});
        } catch (exception) {
            setError(str('importfailed'));
        } finally {
            setImporting(false);
        }
    };

    const dialog = plan ? createElement('div', {
        className: 'modal d-block',
        role: 'dialog',
        'aria-modal': 'true',
        'aria-labelledby': 'gradebook-xp-import-title',
        style: {backgroundColor: 'rgba(0, 0, 0, .4)'}
    }, createElement('div', {className: 'modal-dialog modal-lg'},
        createElement('div', {className: 'modal-content'}, [
            createElement('div', {key: 'header', className: 'modal-header'}, [
                createElement('h5', {key: 'title', id: 'gradebook-xp-import-title', className: 'modal-title'},
                    str('importpreview')),
                createElement('button', {
                    key: 'close', type: 'button', className: 'btn-close', disabled: importing,
                    'aria-label': str('close'), onClick: close
                })
            ]),
            createElement('div', {key: 'body', className: 'modal-body'}, result ? [
                createElement('div', {key: 'success', className: 'alert alert-success'}, str('importsuccess')),
                createElement('ul', {key: 'result-list'}, [
                    createElement('li', {key: 'c'}, `${str('importcreatedcompetencies')}: ${result.createdCompetencies}`),
                    createElement('li', {key: 'r'}, `${str('importcreatedrelations')}: ${result.createdRelations}`),
                    createElement('li', {key: 'a'}, `${str('importcreatedconnections')}: ${result.createdConnections}`),
                    createElement('li', {key: 's'}, `${str('importskippedconnections')}: ${result.skippedConnections}`)
                ]),
                result.failures.length ? createElement('div', {key: 'failures', className: 'alert alert-warning'},
                    `${str('importconnectionfailures')}: ${result.failures.join(', ')}`) : null
            ] : [
                createElement('p', {key: 'intro'}, str('importpreviewhelp')),
                createElement('ul', {key: 'preview-list'}, [
                    createElement('li', {key: 'new'}, `${str('importnewcompetencies')}: ${plan.newCompetencies}`),
                    createElement('li', {key: 'existing'},
                        `${str('importexistingcompetencies')}: ${plan.existingCompetencies}`),
                    createElement('li', {key: 'relations'},
                        `${str('importrelations')}: ${plan.data.relations.length}`),
                    createElement('li', {key: 'matched'},
                        `${str('importmatchedconnections')}: ${plan.matchedConnections}`),
                    createElement('li', {key: 'ambiguous'},
                        `${str('importambiguousconnections')}: ${plan.ambiguousConnections}`),
                    createElement('li', {key: 'missing'},
                        `${str('importmissingconnections')}: ${plan.missingConnections}`)
                ]),
                (plan.ambiguousConnections + plan.missingConnections) > 0 ? createElement('div', {
                    key: 'warning', className: 'alert alert-warning mb-0'
                }, str('importunmatchedwarning')) : null
            ]),
            error ? createElement('div', {key: 'error', className: 'alert alert-danger mx-3'}, error) : null,
            createElement('div', {key: 'footer', className: 'modal-footer'}, [
                createElement('button', {
                    key: 'cancel', type: 'button', className: 'btn btn-secondary', disabled: importing, onClick: close
                }, result ? str('close') : str('cancel')),
                !result ? createElement('button', {
                    key: 'import', type: 'button', className: 'btn btn-primary', disabled: importing, onClick: runImport
                }, importing ? str('importing') : str('import')) : null
            ])
        ])
    )) : null;

    return createElement('div', {className: 'd-inline-block'}, [
        createElement('input', {
            key: 'file', ref: inputRef, type: 'file', accept: 'application/json,.json', className: 'd-none',
            onChange: selectFile
        }),
        createElement('button', {
            key: 'button', type: 'button', className: 'btn btn-outline-primary',
            onClick: () => inputRef.current?.click()
        }, [
            createElement('i', {key: 'icon', className: 'fa fa-upload me-2 mr-2'}),
            str('import')
        ]),
        !plan && error ? createElement('div', {key: 'file-error', className: 'alert alert-danger mt-2'}, error) : null,
        dialog
    ]);
};
