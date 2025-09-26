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
 * Toast notification service for gb_xp_admin gradebook report.
 *
 * @module    gradereport_gb_xp_admin/toast_service
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Toast} from 'theme_boost/toast';

/**
 * Toast notification service using Bootstrap toasts.
 */
export default class ToastService {
    /**
     * Show a toast notification.
     *
     * @param {string} message The toast message
     * @param {string} type The toast type (success, error, info)
     */
    static show(message, type = 'info') {
        const toastContainer = document.getElementById('gb-xp-toast-container');
        if (!toastContainer) {
            return;
        }

        const toastId = `toast-${Date.now()}`;
        const toastType = type === 'error' ? 'danger' : type;
        const autoHide = type !== 'error';

        const toastElement = document.createElement('div');
        toastElement.className = `toast align-items-center text-white bg-${toastType} border-0`;
        toastElement.role = 'alert';
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        toastElement.id = toastId;
        toastElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
            min-width: 300px;
        `;

        toastElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        toastContainer.appendChild(toastElement);

        // Initialize bootstrap toast with appropriate settings
        const toastOptions = autoHide ? {autohide: true, delay: 2000} : {autohide: false};
        const bsToast = new Toast(toastElement, toastOptions);
        bsToast.show();

        // Clean up after hiding
        toastElement.addEventListener('hidden.bs.toast', () => {
            if (toastContainer.contains(toastElement)) {
                toastContainer.removeChild(toastElement);
            }
        });
    }


    // Generic toast types
    static success(message) {
        ToastService.show(message, 'success');
    }

    static error(message) {
        ToastService.show(message, 'error');
    }

    static info(message) {
        ToastService.show(message, 'info');
    }

    // Data loading toasts
    static dataLoaded() {
        ToastService.success('Loaded Gradebook XP data');
    }

    static dataLoadFailed() {
        ToastService.error('Failed to load Gradebook XP data');
    }

    // Competency-specific toasts
    static competencyCreated(name) {
        ToastService.success(`Created competency "${name}"`);
    }

    static competencyUpdated(name) {
        ToastService.success(`Updated competency "${name}"`);
    }

    static competencyDeleted(name) {
        ToastService.success(`Deleted competency "${name}"`);
    }

    static competencyCreateFailed() {
        ToastService.error('Failed to create competency');
    }

    static competencyUpdateFailed() {
        ToastService.error('Failed to update competency');
    }

    static competencyDeleteFailed() {
        ToastService.error('Failed to delete competency');
    }

    // Connection-specific toasts
    static connectionCreated(compName, actName) {
        ToastService.success(`Connected "${compName}" to activity "${actName}"`);
    }

    static connectionDeleted(compName, actName) {
        ToastService.success(`Disconnected "${compName}" from activity "${actName}"`);
    }

    static connectionCreateFailed() {
        ToastService.error('Failed to create connection');
    }

    static connectionDeleteFailed() {
        ToastService.error('Failed to delete connection');
    }

    // Relation-specific toasts
    static relationCreated() {
        ToastService.success('Created competency relation');
    }

    static relationDeleted() {
        ToastService.success('Deleted competency relation');
    }

    static relationCreateFailed() {
        ToastService.error('Failed to create relation');
    }

    static relationDeleteFailed() {
        ToastService.error('Failed to delete relation');
    }
}