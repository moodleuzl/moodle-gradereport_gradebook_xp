// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * EditCompetency component for creating/editing competencies in a modal.
 *
 * @module    gradereport_gb_xp_admin/components/EditCompetency
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gb_xp_admin/hooks/useCompetencyStore';
import {useStrings} from 'gradereport_gb_xp_admin/hooks/useStrings';
import {RelationManager} from 'gradereport_gb_xp_admin/components/RelationManager';

/**
 * EditCompetency modal component for creating/editing competencies.
 *
 * @param {Object} props Component properties
 * @param {boolean} props.show Whether to show the modal
 * @param {Object|null} props.competency The competency to edit (null for new)
 * @param {Object|null} props.defaultParent Default parent for new competencies
 * @param {Function} props.onClose Callback when modal should close
 * @returns {Object} React element
 */
export const EditCompetency = ({show, competency, defaultParent, onClose}) => {
    const {createElement, useState, useEffect} = window.React;
    const {str} = useStrings();
    const {
        createCompetency,
        updateCompetency,
        competencies,
        relations,
        createRelation,
        deleteRelation,
        activities,
        createConnection,
        deleteConnection,
        getConnectionsForCompetency,
        getDescendants,
        getAncestors
    } = useCompetencyStore();

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        maxcomlvl: 1,
        islevelsummed: 1
    });

    const [originalFormData, setOriginalFormData] = useState(null);
    const [saving, setSaving] = useState(false);
    const [createdCompetency, setCreatedCompetency] = useState(null);

    // Update form data when competency prop changes
    useEffect(() => {
        if (show) {
            const initialData = {
                name: competency?.name || '',
                description: competency?.description || '',
                maxcomlvl: competency?.maxcomlvl || 1,
                islevelsummed: competency?.islevelsummed || 1
            };
            setFormData(initialData);
            setOriginalFormData(initialData);
            // Reset createdCompetency when modal is reopened
            setCreatedCompetency(null);
        }
    }, [show, competency]);

    if (!show) {
        return null;
    }

    // Use createdCompetency if it exists (after creation), otherwise use competency prop
    const activeCompetency = createdCompetency || competency;
    const isEditing = !!activeCompetency;
    const modalTitle = isEditing ? str('editcompetency') : str('newcompetency');

    // Check if form has been changed (only for basic fields)
    const hasChanges = originalFormData && (
        formData.name !== originalFormData.name ||
        formData.description !== originalFormData.description ||
        formData.maxcomlvl !== originalFormData.maxcomlvl ||
        formData.islevelsummed !== originalFormData.islevelsummed
    );

    const handleChange = (field, value) => {
        setFormData(prev => ({...prev, [field]: value}));
    };

    const handleSubmit = async(e, shouldClose = false) => {
        e.preventDefault();

        try {
            setSaving(true);

            if (isEditing) {
                await updateCompetency({...formData, id: activeCompetency.id});
                // Update original form data to reflect saved state
                setOriginalFormData({...formData});
                onClose();
            } else {
                // Create new competency
                const newCompetency = await createCompetency(formData);

                // If there's a default parent, create the relation
                if (defaultParent && newCompetency) {
                    await createRelation({
                        parentid: defaultParent.id,
                        childid: newCompetency.id
                    });
                }

                if (shouldClose) {
                    // Close modal after creation
                    onClose();
                } else {
                    // Store the created competency to switch to edit mode
                    setCreatedCompetency(newCompetency);
                    // Update original form data and form data to match new competency
                    const newFormData = {
                        name: newCompetency.name,
                        description: newCompetency.description,
                        maxcomlvl: newCompetency.maxcomlvl,
                        islevelsummed: newCompetency.islevelsummed
                    };
                    setFormData(newFormData);
                    setOriginalFormData(newFormData);
                }
            }
        } catch (error) {
            // Error handling is done in the store
        } finally {
            setSaving(false);
        }
    };

    const handleClose = () => {
        if (!saving) {
            onClose();
        }
    };

    // Get parent and child relationships for current competency
    const getParentRelations = () => {
        if (!activeCompetency) {
            return [];
        }
        return relations.filter(rel => rel.childid === activeCompetency.id);
    };

    const getChildRelations = () => {
        if (!activeCompetency) {
            return [];
        }
        return relations.filter(rel => rel.parentid === activeCompetency.id);
    };

    const getAvailableParents = () => {
        if (!activeCompetency) {
            return competencies.filter(c => c.id !== activeCompetency?.id);
        }
        const existingParentIds = getParentRelations().map(rel => rel.parentid);
        // Get all descendants to prevent cycles - a descendant cannot be a parent
        const descendantIds = getDescendants(activeCompetency.id);
        return competencies.filter(c =>
            c.id !== activeCompetency.id &&
            !existingParentIds.includes(c.id) &&
            !descendantIds.includes(c.id)
        );
    };

    const getAvailableChildren = () => {
        if (!activeCompetency) {
            return competencies.filter(c => c.id !== activeCompetency?.id);
        }
        const existingChildIds = getChildRelations().map(rel => rel.childid);
        // Get all ancestors to prevent cycles - an ancestor cannot be a child
        const ancestorIds = getAncestors(activeCompetency.id);
        return competencies.filter(c =>
            c.id !== activeCompetency.id &&
            !existingChildIds.includes(c.id) &&
            !ancestorIds.includes(c.id)
        );
    };

    const getCompetencyConnections = () => {
        if (!activeCompetency) {
            return [];
        }
        return getConnectionsForCompetency(activeCompetency.id);
    };

    const getAvailableActivities = () => {
        const connectedActivityIds = getCompetencyConnections().map(conn => conn.activityid);
        return activities.filter(activity => !connectedActivityIds.includes(activity.id));
    };

    // Handler for adding parent relations
    const handleAddParents = async(selectedIds) => {
        if (!activeCompetency || selectedIds.length === 0) {
            return;
        }

        for (const selectedId of selectedIds) {
            await createRelation({parentid: selectedId, childid: activeCompetency.id});
        }
    };

    // Handler for adding child relations
    const handleAddChildren = async(selectedIds) => {
        if (!activeCompetency || selectedIds.length === 0) {
            return;
        }

        for (const selectedId of selectedIds) {
            await createRelation({parentid: activeCompetency.id, childid: selectedId});
        }
    };

    // Handler for removing parent relations
    const handleRemoveParent = async(parentRelation) => {
        await deleteRelation(parentRelation.id);
    };

    // Handler for removing child relations
    const handleRemoveChild = async(childRelation) => {
        await deleteRelation(childRelation.id);
    };

    // Handler for adding activity connections
    const handleAddActivities = async(selectedIds) => {
        if (!activeCompetency || selectedIds.length === 0) {
            return;
        }

        for (const activityId of selectedIds) {
            await createConnection({
                competencyid: activeCompetency.id,
                activityid: activityId
            });
        }
    };

    // Handler for removing activity connections
    const handleRemoveActivity = async(connection) => {
        await deleteConnection(connection.id);
    };

    // Map parent relations to display items
    const currentParents = getParentRelations().map(rel => ({
        id: rel.id,
        competencyId: rel.parentid,
        name: competencies.find(c => c.id === rel.parentid)?.name || 'Unknown'
    }));

    // Map child relations to display items
    const currentChildren = getChildRelations().map(rel => ({
        id: rel.id,
        competencyId: rel.childid,
        name: competencies.find(c => c.id === rel.childid)?.name || 'Unknown'
    }));

    // Map connections to display items
    const currentConnections = getCompetencyConnections().map(conn => ({
        id: conn.id,
        activityId: conn.activityid,
        activity: activities.find(a => a.id === conn.activityid)
    }));

    return createElement('div', {
        className: 'modal fade show',
        style: {display: 'block', backgroundColor: 'rgba(0,0,0,0.5)', overflowY: 'auto'}
    }, [
        createElement('div', {
            key: 'modal-dialog',
            className: 'modal-dialog modal-lg',
            style: {maxHeight: 'calc(100vh - 3.5rem)', marginTop: '1.75rem', marginBottom: '1.75rem'}
        }, [
            createElement('div',
                {
                    key: 'modal-content',
                    className: 'modal-content',
                    style: {maxHeight: '100%', display: 'flex', flexDirection: 'column'}
                },
                [
                    // Modal Header
                    createElement('div', {key: 'modal-header', className: 'modal-header', style: {flexShrink: 0}}, [
                        createElement('h5', {key: 'modal-title', className: 'modal-title'}, modalTitle),
                        createElement('button', {
                            key: 'close-button',
                            type: 'button',
                            className: 'btn-close',
                            onClick: handleClose,
                            disabled: saving
                        })
                    ]),

                    // Modal Body with Form
                    createElement('form', {
                        key: 'modal-form',
                        onSubmit: handleSubmit,
                        style: {display: 'flex', flexDirection: 'column', minHeight: 0, flex: '1 1 auto'}
                    }, [
                        createElement('div', {
                            key: 'form-body',
                            className: 'modal-body',
                            style: {overflowY: 'auto', flex: '1 1 auto'}
                        }, [
                            createElement('div', {key: 'row', className: 'row'}, [
                                // Left Column - Basic Form Fields
                                createElement('div', {key: 'left-col', className: 'col-md-6'}, [
                                    // Name Field
                                    createElement('div', {key: 'name-field', className: 'mb-3'}, [
                                        createElement('label', {key: 'name-label', className: 'form-label'}, str('name') + ' *'),
                                        createElement('input', {
                                            key: 'name-input',
                                            type: 'text',
                                            className: 'form-control',
                                            value: formData.name,
                                            onChange: (e) => handleChange('name', e.target.value),
                                            required: true,
                                            disabled: saving
                                        })
                                    ]),

                                    // Description Field
                                    createElement('div', {key: 'description-field', className: 'mb-3'}, [
                                        createElement('label', {
                                            key: 'description-label',
                                            className: 'form-label'
                                        }, str('description')),
                                        createElement('textarea', {
                                            key: 'description-input',
                                            className: 'form-control',
                                            rows: 3,
                                            value: formData.description,
                                            onChange: (e) => handleChange('description', e.target.value),
                                            disabled: saving
                                        })
                                    ]),

                                    // Maximum Level Field
                                    createElement('div', {key: 'maxlevel-field', className: 'mb-3'}, [
                                        createElement('label', {
                                            key: 'maxlevel-label',
                                            className: 'form-label'
                                        }, str('maxcomlvl') + ' *'),
                                        createElement('input', {
                                            key: 'maxlevel-input',
                                            type: 'number',
                                            className: 'form-control',
                                            min: 1,
                                            value: formData.maxcomlvl,
                                            onChange: (e) => handleChange('maxcomlvl', parseInt(e.target.value) || 1),
                                            required: true,
                                            disabled: saving
                                        })
                                    ]),

                                    // Is Level Summed Checkbox
                                    createElement('div', {key: 'levelsummed-field', className: 'mb-3'}, [
                                        createElement('div', {key: 'checkbox-wrapper', className: 'form-check'}, [
                                            createElement('input', {
                                                key: 'levelsummed-input',
                                                type: 'checkbox',
                                                className: 'form-check-input',
                                                id: 'islevelsummed',
                                                checked: formData.islevelsummed === 1,
                                                onChange: (e) => handleChange('islevelsummed', e.target.checked ? 1 : 0),
                                                disabled: saving
                                            }),
                                            createElement('label', {
                                                key: 'levelsummed-label',
                                                className: 'form-check-label',
                                                htmlFor: 'islevelsummed'
                                            }, str('islevelsummed'))
                                        ])
                                    ])
                                ]),

                                // Right Column - Relationship Management
                                isEditing ? createElement('div', {key: 'right-col', className: 'col-md-6'}, [
                                    // Parent Competencies Section
                                    createElement(RelationManager, {
                                        key: 'parents-manager',
                                        title: str('parent'),
                                        addButtonText: str('addparents'),
                                        addButtonClass: 'btn-outline-primary',
                                        currentItems: currentParents,
                                        availableItems: getAvailableParents(),
                                        onAdd: handleAddParents,
                                        onRemove: handleRemoveParent,
                                        renderItem: (item) => item.name,
                                        emptyMessage: str('noparentcompetencies'),
                                        selectorTitle: str('selectcompetencies').replace('{$a}', str('parent').toLowerCase()),
                                        searchPlaceholder: str('searchcompetencies'),
                                        idPrefix: 'parent',
                                        disabled: saving
                                    }),

                                    // Child Competencies Section
                                    createElement(RelationManager, {
                                        key: 'children-manager',
                                        title: str('children'),
                                        addButtonText: str('addchildren'),
                                        addButtonClass: 'btn-outline-primary',
                                        currentItems: currentChildren,
                                        availableItems: getAvailableChildren(),
                                        onAdd: handleAddChildren,
                                        onRemove: handleRemoveChild,
                                        renderItem: (item) => item.name,
                                        emptyMessage: str('nochildcompetencies'),
                                        selectorTitle: str('selectcompetencies').replace('{$a}', str('children').toLowerCase()),
                                        searchPlaceholder: str('searchcompetencies'),
                                        idPrefix: 'child',
                                        disabled: saving
                                    }),

                                    // Connected Activities Section
                                    createElement(RelationManager, {
                                        key: 'activities-manager',
                                        title: str('connections') + ' ' + str('activities'),
                                        addButtonText: str('addactivities'),
                                        addButtonClass: 'btn-outline-success',
                                        currentItems: currentConnections,
                                        availableItems: getAvailableActivities(),
                                        onAdd: handleAddActivities,
                                        onRemove: handleRemoveActivity,
                                        renderItem: (item) => {
                                            // Handle both connection objects (currentItems) and activity objects (availableItems)
                                            if (item.activity) {
                                                // Connection object with nested activity
                                                return `${item.activity.name} (${item.activity.module})`;
                                            } else if (item.name) {
                                                // Raw activity object
                                                return `${item.name} (${item.module})`;
                                            } else {
                                                return `Unknown (${item.id})`;
                                            }
                                        },
                                        emptyMessage: str('noconnectedactivities'),
                                        selectorTitle: str('selectactivities'),
                                        searchPlaceholder: str('searchactivities'),
                                        idPrefix: 'activity',
                                        disabled: saving
                                    })
                                ]) : null
                            ])
                        ]),

                        // Modal Footer with Buttons
                        createElement('div', {key: 'modal-footer', className: 'modal-footer'}, [
                            // Cancel/Close button
                            createElement('button', {
                                key: 'cancel',
                                type: 'button',
                                className: 'btn btn-secondary',
                                onClick: handleClose,
                                disabled: saving
                            }, isEditing ? (hasChanges ? str('cancel') : str('close')) : str('cancel')),

                            // Create mode: Create button (closes modal)
                            !isEditing ? createElement('button', {
                                key: 'create',
                                type: 'button',
                                className: 'btn btn-primary mr-2',
                                onClick: (e) => handleSubmit(e, true),
                                disabled: saving
                            }, saving ? str('creating') : str('create')) : null,

                            // Create mode: Create and Edit button (stays open)
                            !isEditing ? createElement('button', {
                                key: 'create-edit',
                                type: 'submit',
                                className: 'btn btn-success',
                                disabled: saving
                            }, saving ? str('creating') : str('createandedit')) : null,

                            // Edit mode: Update button (disabled if no changes)
                            isEditing ? createElement('button', {
                                key: 'update',
                                type: 'submit',
                                className: 'btn btn-primary',
                                disabled: saving || !hasChanges
                            }, saving ? str('updating') : str('update')) : null
                        ])
                    ])
                ])
        ])
    ]);
};