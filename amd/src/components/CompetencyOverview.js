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
 * CompetencyOverview component showing list of competencies.
 *
 * @module    gradereport_gb_xp_admin/components/CompetencyOverview
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gb_xp_admin/hooks/useCompetencyStore';
import {CompetencyCard} from 'gradereport_gb_xp_admin/components/CompetencyCard';
import {ConfirmDialog} from 'gradereport_gb_xp_admin/components/ConfirmDialog';
import {EditCompetency} from 'gradereport_gb_xp_admin/components/EditCompetency';

/**
 * CompetencyOverview component showing list of competencies.
 *
 * @returns {Object} React element
 */
export const CompetencyOverview = () => {
    const {createElement, useState} = window.React;
    const {competencies, deleteCompetency} = useCompetencyStore();

    const [confirmDialog, setConfirmDialog] = useState({show: false, competencyId: null, competencyName: ''});
    const [showForm, setShowForm] = useState(false);
    const [editingCompetency, setEditingCompetency] = useState(null);

    const handleAddCompetency = () => {
        setEditingCompetency(null);
        setShowForm(true);
    };

    const handleEditCompetency = (competency) => {
        setEditingCompetency(competency);
        setShowForm(true);
    };

    const handleCloseForm = () => {
        setShowForm(false);
        setEditingCompetency(null);
    };

    const handleDeleteRequest = (competencyId) => {
        const competency = competencies.find(c => c.id === competencyId);
        setConfirmDialog({
            show: true,
            competencyId,
            competencyName: competency?.name || 'Unknown'
        });
    };

    const handleConfirmDelete = async () => {
        try {
            await deleteCompetency(confirmDialog.competencyId);
            setConfirmDialog({show: false, competencyId: null, competencyName: ''});
        } catch (error) {
            // Error handling is done in the store
        }
    };

    const handleCancelDelete = () => {
        setConfirmDialog({show: false, competencyId: null, competencyName: ''});
    };

    // Show empty state
    if (!competencies.length) {
        return createElement('div', {}, [
            createElement('div', {key: 'empty-state', className: 'text-center py-5'}, [
                createElement('h4', {key: 'no-data', className: 'text-muted mb-3'}, 'No competencies found'),
                createElement('button', {
                    key: 'add-first',
                    type: 'button',
                    className: 'btn btn-primary',
                    onClick: handleAddCompetency
                }, 'Add Your First Competency')
            ]),
            createElement(EditCompetency, {
                key: 'edit-modal',
                show: showForm,
                competency: editingCompetency,
                onClose: handleCloseForm
            })
        ]);
    }

    // Show competencies list
    return createElement('div', {}, [
        createElement('div', {key: 'header', className: 'd-flex justify-content-between align-items-center mb-4'}, [
            createElement('h3', {key: 'title'}, 'Competencies'),
            createElement('button', {
                key: 'add',
                type: 'button',
                className: 'btn btn-primary',
                onClick: handleAddCompetency
            }, 'Add Competency')
        ]),
        createElement('div', {key: 'cards', className: 'row'},
            competencies.map(competency =>
                createElement('div', {
                    key: competency.id,
                    className: 'col-md-6 col-lg-4'
                }, createElement(CompetencyCard, {
                    competency,
                    onEdit: handleEditCompetency,
                    onDelete: handleDeleteRequest
                }))
            )
        ),
        createElement(ConfirmDialog, {
            key: 'confirm-dialog',
            show: confirmDialog.show,
            title: 'Confirm Delete',
            message: `Are you sure you want to delete the competency "${confirmDialog.competencyName}"?
            This action cannot be undone.`,
            onConfirm: handleConfirmDelete,
            onCancel: handleCancelDelete
        }),
        createElement(EditCompetency, {
            key: 'edit-modal',
            show: showForm,
            competency: editingCompetency,
            onClose: handleCloseForm
        })
    ]);
};