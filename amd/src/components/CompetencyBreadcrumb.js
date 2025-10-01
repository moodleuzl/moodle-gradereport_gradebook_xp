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
 * CompetencyBreadcrumb component for navigation trail.
 *
 * @module    gradereport_gb_xp_admin/components/CompetencyBreadcrumb
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useStrings} from 'gradereport_gb_xp_admin/hooks/useStrings';

/**
 * CompetencyBreadcrumb component displays navigation trail.
 *
 * @param {Object} props Component properties
 * @param {Array} props.breadcrumbPath Array of competency objects in the path
 * @param {Function} props.onNavigate Callback when breadcrumb is clicked (competency) => void
 * @param {boolean} props.showEllipsis Whether to show "..." for abbreviated paths (path length = 1)
 * @returns {Object} React element
 */
export const CompetencyBreadcrumb = ({breadcrumbPath, onNavigate, showEllipsis = true}) => {
    const {createElement} = window.React;
    const {str} = useStrings();

    if (!breadcrumbPath || breadcrumbPath.length === 0) {
        return createElement('nav', {
            'aria-label': 'breadcrumb',
            className: 'mb-0'
        }, [
            createElement('ol', {key: 'breadcrumb', className: 'breadcrumb mb-0'}, [
                createElement('li', {
                    key: 'root',
                    className: 'breadcrumb-item active',
                    'aria-current': 'page'
                }, str('root'))
            ])
        ]);
    }

    // If path has only 1 item and showEllipsis is true, show abbreviated form: Root / ... / Item
    const isAbbreviated = showEllipsis && breadcrumbPath.length === 1;

    return createElement('nav', {
        'aria-label': 'breadcrumb',
        className: 'mb-0'
    }, [
        createElement('ol', {key: 'breadcrumb', className: 'breadcrumb mb-0'}, [
            // Root link
            createElement('li', {key: 'root', className: 'breadcrumb-item'}, [
                createElement('a', {
                    key: 'root-link',
                    href: '#',
                    onClick: (e) => {
                        e.preventDefault();
                        onNavigate(null);
                    }
                }, str('root'))
            ]),
            // Show ellipsis for abbreviated paths
            ...(isAbbreviated ? [
                createElement('li', {
                    key: 'ellipsis',
                    className: 'breadcrumb-item'
                }, '...')
            ] : []),
            // Path items
            ...breadcrumbPath.map((competency, index) => {
                const isLast = index === breadcrumbPath.length - 1;

                if (isLast) {
                    return createElement('li', {
                        key: competency.id,
                        className: 'breadcrumb-item active',
                        'aria-current': 'page'
                    }, competency.name);
                } else {
                    return createElement('li', {
                        key: competency.id,
                        className: 'breadcrumb-item'
                    }, [
                        createElement('a', {
                            key: 'link',
                            href: '#',
                            onClick: (e) => {
                                e.preventDefault();
                                onNavigate(competency);
                            }
                        }, competency.name)
                    ]);
                }
            })
        ])
    ]);
};