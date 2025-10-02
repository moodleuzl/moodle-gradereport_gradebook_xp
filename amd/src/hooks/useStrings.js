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
 * String localization hook with prefetching.
 *
 * @module    gradereport_gb_xp_admin/hooks/useStrings
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';

const {createContext, useContext, useState, useEffect} = window.React;

// Create Context
const StringContext = createContext();

// List of all strings to prefetch
const STRING_KEYS = [
    // General
    'pluginname',
    'name',
    'description',

    // Competency management
    'managecompetencies',
    'newcompetency',
    'editcompetency',
    'deletecompetency',
    'addcompetency',
    'nocompetencies',

    // Form fields
    'maxcomlvl',
    'levelcalcmethod',

    // Actions
    'export',
    'import',
    'cancel',
    'close',
    'create',
    'update',
    'save',
    'delete',
    'edit',
    'search',

    // Relations
    'parent',
    'children',
    'activities',
    'connections',

    // Messages
    'createcompetencysuccess',
    'updatecompetencysuccess',
    'cancelcompetency',
    'missingname',
    'missinginput',

    // Toast messages
    'dataLoaded',
    'dataLoadFailed',
    'competencyCreated',
    'competencyUpdated',
    'competencyDeleted',
    'competencyCreateFailed',
    'competencyUpdateFailed',
    'competencyDeleteFailed',
    'connectionCreated',
    'connectionDeleted',
    'connectionCreateFailed',
    'connectionDeleteFailed',
    'relationCreated',
    'relationDeleted',
    'relationCreateFailed',
    'relationDeleteFailed',

    // UI elements
    'addparents',
    'addchildren',
    'addactivities',
    'noparentcompetencies',
    'nochildcompetencies',
    'noconnectedactivities',
    'selectcompetencies',
    'selectactivities',
    'searchcompetencies',
    'searchactivities',
    'addselected',
    'remove',
    'noitemsfound',

    // Confirm dialog
    'confirmdelete',
    'confirmdeletemessage',
    'confirmaction',

    // Breadcrumb
    'root',

    // List items
    'maxlevel',
    'levelsummed',

    // Competency form
    'createandedit',
    'saving',
    'creating',
    'updating',
    'islevelsummed',
    'nochildcompetenciesfound',
    'addsubcompetency',
    'editselectedcompetency',
    'deleteselectedcompetency',

    // Search
    'searchplaceholder',
    'mincharacters',

    // Export
    'exportdata',
    'exportalldata'
];

// Custom hook to use strings
export const useStrings = () => {
    const context = useContext(StringContext);
    if (!context) {
        throw new Error('useStrings must be used within a StringProvider');
    }
    return context;
};

// Provider component
export const StringProvider = ({children}) => {
    const [strings, setStrings] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadStrings = async() => {
            try {
                setLoading(true);

                // Fetch all strings in parallel
                const stringPromises = STRING_KEYS.map(key =>
                    getString(key, 'gradereport_gb_xp_admin')
                        .then(value => ({key, value}))
                        .catch(() => ({key, value: key})) // Fallback to key if string not found
                );

                const results = await Promise.all(stringPromises);

                // Convert array to object
                const stringMap = {};
                results.forEach(({key, value}) => {
                    stringMap[key] = value;
                });

                setStrings(stringMap);
            } catch (error) {
                window.console.error('Failed to load strings:', error);
            } finally {
                setLoading(false);
            }
        };

        loadStrings();
    }, []);

    // Helper function to get a string by key
    const str = (key, defaultValue = '') => {
        return strings[key] || defaultValue || key;
    };

    const value = {
        strings,
        loading,
        str
    };

    return window.React.createElement(StringContext.Provider, {value}, children);
};
