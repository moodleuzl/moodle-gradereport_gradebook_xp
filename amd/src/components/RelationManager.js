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
 * RelationManager component that combines list and selector.
 *
 * @module    gradereport_gb_xp_admin/components/RelationManager
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {RelationSelector} from 'gradereport_gb_xp_admin/components/RelationSelector';
import {RelationList} from 'gradereport_gb_xp_admin/components/RelationList';

/**
 * RelationManager component that manages selection and display of related items.
 *
 * @param {Object} props Component properties
 * @param {string} props.title Section title
 * @param {string} props.addButtonText Text for add button
 * @param {string} props.addButtonClass Bootstrap button class for add button
 * @param {Array} props.currentItems Array of currently related items
 * @param {Array} props.availableItems Array of items available for selection
 * @param {Function} props.onAdd Callback when items are added (selectedIds) => Promise
 * @param {Function} props.onRemove Callback when item is removed (item) => Promise
 * @param {Function} props.renderItem Function to render item display (item) => string
 * @param {string} props.emptyMessage Message when no items
 * @param {string} props.selectorTitle Title for selector
 * @param {string} props.searchPlaceholder Placeholder for search
 * @param {string} props.idPrefix Prefix for checkbox IDs
 * @param {boolean} props.disabled Whether controls are disabled
 * @returns {Object} React element
 */
export const RelationManager = ({
    title,
    addButtonText,
    addButtonClass = 'btn-outline-primary',
    currentItems,
    availableItems,
    onAdd,
    onRemove,
    renderItem,
    emptyMessage,
    selectorTitle,
    searchPlaceholder,
    idPrefix,
    disabled = false
}) => {
    const {createElement, useState} = window.React;
    const [showSelector, setShowSelector] = useState(false);
    const [selectedIds, setSelectedIds] = useState([]);

    const handleShowSelector = () => {
        setSelectedIds([]);
        setShowSelector(true);
    };

    const handleToggleSelection = (itemId) => {
        setSelectedIds(prev =>
            prev.includes(itemId)
                ? prev.filter(id => id !== itemId)
                : [...prev, itemId]
        );
    };

    const handleAdd = async () => {
        if (selectedIds.length === 0) {
            return;
        }

        try {
            await onAdd(selectedIds);
            setSelectedIds([]);
            setShowSelector(false);
        } catch (error) {
            // Error handling is done in parent
        }
    };

    const handleCancel = () => {
        setSelectedIds([]);
        setShowSelector(false);
    };

    return createElement('div', {className: 'mb-3'}, [
        // Header
        createElement('div', {
            key: 'header',
            className: 'd-flex justify-content-between align-items-center mb-2'
        }, [
            createElement('h6', {key: 'title', className: 'mb-0'}, title),
            createElement('button', {
                key: 'add-button',
                type: 'button',
                className: `btn btn-sm ${addButtonClass}`,
                onClick: handleShowSelector,
                disabled: disabled
            }, addButtonText)
        ]),

        // Selector or List
        showSelector ?
            createElement(RelationSelector, {
                key: 'selector',
                items: availableItems,
                selectedIds: selectedIds,
                onToggle: handleToggleSelection,
                onAdd: handleAdd,
                onCancel: handleCancel,
                title: selectorTitle,
                searchPlaceholder: searchPlaceholder,
                renderItem: renderItem,
                idPrefix: idPrefix
            })
        :
            createElement(RelationList, {
                key: 'list',
                items: currentItems,
                onRemove: onRemove,
                emptyMessage: emptyMessage,
                renderItem: renderItem
            })
    ]);
};