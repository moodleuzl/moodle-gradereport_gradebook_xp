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
 * React application for gb_xp_admin gradebook report.
 *
 * @module    gradereport_gb_xp_admin/react_app
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore, CompetencyProvider} from 'gradereport_gb_xp_admin/hooks/useCompetencyStore';
import {CompetencyOverview} from 'gradereport_gb_xp_admin/components/CompetencyOverview';

/**
 * Main React component for the gradebook admin interface.
 *
 * @param {Object} options Component properties
 * @returns {Object} React element
 */
const App = (options) => {
    const {createElement} = window.React;
    const {loading} = useCompetencyStore();

    if (loading) {
        return createElement('div', {className: 'loading text-center py-5'}, 'Loading competencies...');
    }

    // Main interface
    return createElement('div', {className: 'gb-xp-admin-app container-fluid py-4'}, [
        createElement('div', {key: 'header', className: 'app-header mb-4'}, [
            createElement('h2', {key: 'title', className: 'mb-0'}, options.title || 'Competency Management'),
        ]),
        createElement('div', {key: 'content'},
            createElement(CompetencyOverview)
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
            createElement(CompetencyProvider, {
                courseid: options.courseid
            }, createElement(App, {
                ...options,
            }))
        );
    }
};
