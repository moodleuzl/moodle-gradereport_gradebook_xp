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
 * API service for gb_xp_admin gradebook report.
 *
 * @module    gradereport_gb_xp_admin/api_service
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call} from 'core/ajax';

/**
 * API service class for making calls to Moodle external functions.
 */
export default class ApiService {
    /**
     * Get competencies for a course.
     *
     * @param {number} courseid Course ID
     * @returns {Promise} Promise resolving to competencies
     */
    static async getCompetencies(courseid) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_get_competencies',
            args: {courseid}
        }]);
        return await promises[0];
    }

    /**
     * Create a new competency.
     *
     * @param {Object} data Competency data
     * @returns {Promise} Promise resolving to created competency
     */
    static async createCompetency(data) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_create_competency',
            args: data
        }]);
        return await promises[0];
    }

    /**
     * Update a competency.
     *
     * @param {Object} data Competency data
     * @returns {Promise} Promise resolving to updated competency
     */
    static async updateCompetency(data) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_update_competency',
            args: data
        }]);
        return await promises[0];
    }

    /**
     * Delete a competency.
     *
     * @param {number} id Competency ID
     * @returns {Promise} Promise resolving to success
     */
    static async deleteCompetency(id) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_delete_competency',
            args: {id}
        }]);
        return await promises[0];
    }

    /**
     * Get activities for a course.
     *
     * @param {number} courseid Course ID
     * @returns {Promise} Promise resolving to activities
     */
    static async getActivities(courseid) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_get_activities',
            args: {courseid}
        }]);
        return await promises[0];
    }

    /**
     * Get connections for a course.
     *
     * @param {number} courseid Course ID
     * @returns {Promise} Promise resolving to connections
     */
    static async getConnections(courseid) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_get_connections',
            args: {courseid}
        }]);
        return await promises[0];
    }

    /**
     * Create a connection between competency and activity.
     *
     * @param {Object} data Connection data
     * @returns {Promise} Promise resolving to created connection
     */
    static async createConnection(data) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_create_connection',
            args: data
        }]);
        return await promises[0];
    }

    /**
     * Delete a connection.
     *
     * @param {number} id Connection ID
     * @returns {Promise} Promise resolving to success
     */
    static async deleteConnection(id) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_delete_connection',
            args: {id}
        }]);
        return await promises[0];
    }

    /**
     * Get relations for a course.
     *
     * @param {number} courseid Course ID
     * @returns {Promise} Promise resolving to relations
     */
    static async getRelations(courseid) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_get_relations',
            args: {courseid}
        }]);
        return await promises[0];
    }

    /**
     * Create a relation between competencies.
     *
     * @param {Object} data Relation data
     * @returns {Promise} Promise resolving to created relation
     */
    static async createRelation(data) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_create_relation',
            args: data
        }]);
        return await promises[0];
    }

    /**
     * Delete a relation.
     *
     * @param {number} id Relation ID
     * @returns {Promise} Promise resolving to success
     */
    static async deleteRelation(id) {
        const promises = call([{
            methodname: 'gradereport_gb_xp_admin_delete_relation',
            args: {id}
        }]);
        return await promises[0];
    }
}