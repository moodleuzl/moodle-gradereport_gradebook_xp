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
 * RelationSelector component for selecting items with fuzzy search.
 *
 * @module    gradereport_gb_xp_admin/components/RelationSelector
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useStrings} from 'gradereport_gb_xp_admin/hooks/useStrings';

/**
 * Fuzzy search implementation.
 * Returns true if searchTerm characters appear in text in order (case-insensitive).
 *
 * @param {string} text - Text to search in
 * @param {string} searchTerm - Search term
 * @returns {boolean} True if match found
 */
const fuzzyMatch = (text, searchTerm) => {
    if (!searchTerm) {
        return true;
    }

    const textLower = text.toLowerCase();
    const searchLower = searchTerm.toLowerCase();

    let searchIndex = 0;
    for (let i = 0; i < textLower.length && searchIndex < searchLower.length; i++) {
        if (textLower[i] === searchLower[searchIndex]) {
            searchIndex++;
        }
    }

    return searchIndex === searchLower.length;
};

/**
 * RelationSelector component for selecting items with checkboxes and search.
 *
 * @param {Object} props Component properties
 * @param {Array} props.items Array of items to display
 * @param {Array} props.selectedIds Array of selected item IDs
 * @param {Function} props.onToggle Callback when item is toggled
 * @param {Function} props.onAdd Callback when Add Selected is clicked
 * @param {Function} props.onCancel Callback when Cancel is clicked
 * @param {string} props.title Title text
 * @param {string} props.searchPlaceholder Placeholder for search input
 * @param {Function} props.renderItem Function to render item label (item) => string
 * @param {string} props.idPrefix Prefix for checkbox IDs
 * @returns {Object} React element
 */
export const RelationSelector = ({
    items,
    selectedIds,
    onToggle,
    onAdd,
    onCancel,
    title,
    searchPlaceholder,
    renderItem,
    idPrefix
}) => {
    const {createElement, useState} = window.React;
    const {str} = useStrings();
    const [searchTerm, setSearchTerm] = useState('');

    // Filter items based on fuzzy search
    const filteredItems = items.filter(item =>
        fuzzyMatch(renderItem(item), searchTerm)
    );

    return createElement('div', {className: 'border rounded p-2'}, [
        createElement('div', {key: 'selector-header', className: 'mb-2'}, [
            createElement('small', {key: 'selector-text', className: 'text-muted'}, title)
        ]),

        // Search input
        createElement('div', {key: 'search-field', className: 'mb-2'}, [
            createElement('input', {
                key: 'search-input',
                type: 'text',
                className: 'form-control form-control-sm',
                placeholder: searchPlaceholder,
                value: searchTerm,
                onChange: (e) => setSearchTerm(e.target.value)
            })
        ]),

        // Items list
        createElement('div', {
            key: 'items-list',
            className: 'mb-2',
            style: {maxHeight: '120px', overflowY: 'auto'}
        },
            filteredItems.length > 0 ?
                filteredItems.map(item =>
                    createElement('div', {key: item.id, className: 'form-check'}, [
                        createElement('input', {
                            key: 'checkbox',
                            type: 'checkbox',
                            className: 'form-check-input',
                            id: `${idPrefix}-${item.id}`,
                            checked: selectedIds.includes(item.id),
                            onChange: () => onToggle(item.id)
                        }),
                        createElement('label', {
                            key: 'label',
                            className: 'form-check-label',
                            htmlFor: `${idPrefix}-${item.id}`
                        }, renderItem(item))
                    ])
                )
            :
                createElement('div', {
                    key: 'no-results',
                    className: 'text-muted small text-center py-2'
                }, str('noitemsfound'))
        ),

        // Action buttons
        createElement('div', {key: 'selector-buttons', className: 'd-flex'}, [
            createElement('button', {
                key: 'add',
                type: 'button',
                className: 'btn btn-sm btn-success mr-2',
                onClick: onAdd,
                disabled: selectedIds.length === 0
            }, str('addselected')),
            createElement('button', {
                key: 'cancel',
                type: 'button',
                className: 'btn btn-sm btn-secondary',
                onClick: onCancel
            }, str('cancel'))
        ])
    ]);
};