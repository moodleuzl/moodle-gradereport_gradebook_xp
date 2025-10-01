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
 * RelationList component for displaying a list of related items.
 *
 * @module    gradereport_gb_xp_admin/components/RelationList
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * RelationList component for displaying items with remove buttons.
 *
 * @param {Object} props Component properties
 * @param {Array} props.items Array of items to display
 * @param {Function} props.onRemove Callback when remove is clicked (item) => void
 * @param {string} props.emptyMessage Message to show when list is empty
 * @param {Function} props.renderItem Function to render item display (item) => string|element
 * @returns {Object} React element
 */
export const RelationList = ({items, onRemove, emptyMessage, renderItem}) => {
    const {createElement} = window.React;

    return createElement('div', {
        className: 'border rounded p-2',
        style: {minHeight: '60px', maxHeight: '120px', overflowY: 'auto'}
    },
        items.length > 0 ?
            items.map(item =>
                createElement('div', {
                    key: item.id,
                    className: 'd-flex justify-content-between align-items-center py-1'
                }, [
                    createElement('span', {key: 'name', className: 'small'}, renderItem(item)),
                    createElement('button', {
                        key: 'remove',
                        type: 'button',
                        className: 'btn btn-sm btn-outline-danger',
                        onClick: () => onRemove(item)
                    }, '×')
                ])
            )
        :
            createElement('div', {
                key: 'empty',
                className: 'text-muted small text-center py-2'
            }, emptyMessage)
    );
};