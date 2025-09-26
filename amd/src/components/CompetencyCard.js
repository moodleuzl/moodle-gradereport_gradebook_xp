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
 * CompetencyCard component for displaying competency information.
 *
 * @module    gradereport_gb_xp_admin/components/CompetencyCard
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompetencyCard component for displaying individual competency as a Bootstrap card.
 *
 * @param {Object} props Component properties
 * @param {Object} props.competency The competency data
 * @param {Function} props.onEdit Callback when edit button is clicked
 * @param {Function} props.onDelete Callback when delete button is clicked
 * @returns {Object} React element
 */
export const CompetencyCard = ({competency, onEdit, onDelete}) => {
    const {createElement} = window.React;

    const handleEdit = () => {
        onEdit(competency);
    };

    const handleDelete = () => {
        onDelete(competency.id);
    };

    return createElement('div', {className: 'card mb-3'}, [
        createElement('div', {key: 'card-body', className: 'card-body'}, [
            createElement('h5', {key: 'title', className: 'card-title'}, competency.name),
            createElement('p', {key: 'description', className: 'card-text'},
                competency.description || 'No description available'
            ),
            createElement('p', {key: 'level', className: 'card-text'}, [
                createElement('small', {key: 'level-text', className: 'text-muted'},
                    `Maximum Level: ${competency.maxcomlvl}`
                )
            ]),
            createElement('div', {key: 'actions', className: 'd-flex gap-2'}, [
                createElement('button', {
                    key: 'edit',
                    type: 'button',
                    className: 'btn btn-primary btn-sm',
                    onClick: handleEdit
                }, 'Edit'),
                createElement('button', {
                    key: 'delete',
                    type: 'button',
                    className: 'btn btn-danger btn-sm',
                    onClick: handleDelete
                }, 'Delete')
            ])
        ])
    ]);
};