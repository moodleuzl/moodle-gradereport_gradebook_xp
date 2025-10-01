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
 * CompetencyListItem component for displaying a single competency in the tree view.
 *
 * @module    gradereport_gb_xp_admin/components/CompetencyListItem
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompetencyListItem component displays a single competency with actions.
 *
 * @param {Object} props Component properties
 * @param {Object} props.competency The competency object
 * @param {number} props.childCount Number of child competencies
 * @param {Function} props.onSelect Callback when competency is selected
 * @param {Function} props.onEdit Callback when edit button is clicked
 * @param {Function} props.onDelete Callback when delete button is clicked
 * @returns {Object} React element
 */
export const CompetencyListItem = ({competency, childCount, onSelect, onEdit, onDelete}) => {
    const {createElement} = window.React;

    return createElement('div', {
        className: 'list-group-item list-group-item-action',
        style: {cursor: 'pointer'}
    }, [
        createElement('div', {
            key: 'content',
            className: 'd-flex justify-content-between align-items-start'
        }, [
            // Left side - competency info (clickable)
            createElement('div', {
                key: 'info',
                className: 'flex-grow-1',
                onClick: () => onSelect(competency),
                style: {cursor: 'pointer'}
            }, [
                createElement('div', {key: 'header', className: 'd-flex align-items-center gap-2'}, [
                    createElement('h5', {key: 'name', className: 'mb-1'}, competency.name),
                    childCount > 0 ? createElement('span', {
                        key: 'badge',
                        className: 'badge bg-secondary'
                    }, `${childCount} ${childCount === 1 ? 'child' : 'children'}`) : null
                ]),
                competency.description ? createElement('p', {
                    key: 'description',
                    className: 'mb-1 text-muted small'
                }, competency.description) : null,
                createElement('small', {key: 'meta', className: 'text-muted'}, [
                    `Max Level: ${competency.maxcomlvl}`,
                    competency.islevelsummed === 1 ? ' • Level Summed' : ''
                ])
            ]),

            // Right side - action buttons
            createElement('div', {
                key: 'actions',
                className: 'd-flex gap-2 ms-3',
                style: {flexShrink: 0}
            }, [
                createElement('button', {
                    key: 'edit',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-primary',
                    onClick: (e) => {
                        e.stopPropagation();
                        onEdit(competency);
                    },
                    title: 'Edit competency'
                }, 'Edit'),
                createElement('button', {
                    key: 'delete',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-danger',
                    onClick: (e) => {
                        e.stopPropagation();
                        onDelete(competency.id);
                    },
                    title: 'Delete competency'
                }, 'Delete')
            ])
        ])
    ]);
};