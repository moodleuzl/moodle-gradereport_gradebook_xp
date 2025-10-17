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
 * React application for gradebook_xp gradebook report.
 *
 * @module    gradereport_gradebook_xp/react_app
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore, CompetencyProvider} from 'gradereport_gradebook_xp/hooks/useCompetencyStore';
import {useStrings, StringProvider} from 'gradereport_gradebook_xp/hooks/useStrings';
import {CompetencyTreeView} from 'gradereport_gradebook_xp/components/CompetencyTreeView';

/**
 * Main React component for the gradebook admin interface.
 *
 * @returns {Object} React element
 */
const CompetencyManager = () => {
    const {createElement} = window.React;
    const {loading: dataLoading} = useCompetencyStore();
    const {loading: stringsLoading, str} = useStrings();

    if (dataLoading || stringsLoading) {
        return createElement('div', {className: 'loading text-center py-5'},
            stringsLoading ? 'Loading...' : str('loadingcompetencies', 'Loading competencies...')
        );
    }

    // Main interface
    return createElement('div', {className: 'gb-xp-admin-app container-fluid py-4'}, [
        createElement('div', {key: 'content'},
            createElement(CompetencyTreeView)
        )
    ]);
};

/**
 * Initialize the React application.
 *
 * @param {string} containerId Parameters object
 * @param {Object} options Configuration options
 */
export const init = (containerId, options = {}) => {
    if (!window.React || !window.ReactDOM) {
        // Use proper error logging
        if (window.console && window.console.error) {
            window.console.error('React or ReactDOM not loaded globally');
        }
        return;
    }

    const container = document.getElementById(containerId);
    if (container) {
        const {createElement} = window.React;
        const {createRoot} = window.ReactDOM;

        const root = createRoot(container);
        root.render(
            createElement(StringProvider, {key: "string-provider"},
                createElement(CompetencyProvider, {
                    courseid: options.courseid
                }, createElement(CompetencyManager, {key: "competency-manager"}))
            )
        );
    }
};
