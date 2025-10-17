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
 * Toast notification service for gradebook_xp gradebook report.
 *
 * @module    gradereport_gradebook_xp/toast_service
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Toast} from 'theme_boost/toast';
import {getString} from 'core/str';

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
        const toastOptions = autoHide ? {autohide: true, delay: 3000} : {autohide: false};
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
    static async dataLoaded() {
        const msg = await getString('dataloaded', 'gradereport_gradebook_xp');
        ToastService.success(msg);
    }

    static async dataLoadFailed() {
        const msg = await getString('dataloadfailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    // Competency-specific toasts
    static async competencyCreated(name) {
        const msg = await getString('competencycreated', 'gradereport_gradebook_xp', name);
        ToastService.success(msg);
    }

    static async competencyUpdated(name) {
        const msg = await getString('competencyupdated', 'gradereport_gradebook_xp', name);
        ToastService.success(msg);
    }

    static async competencyDeleted(name) {
        const msg = await getString('competencydeleted', 'gradereport_gradebook_xp', name);
        ToastService.success(msg);
    }

    static async competencyCreateFailed() {
        const msg = await getString('competencycreatefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    static async competencyUpdateFailed() {
        const msg = await getString('competencyupdatefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    static async competencyDeleteFailed() {
        const msg = await getString('competencydeletefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    // Connection-specific toasts
    static async connectionCreated(compName, actName) {
        const msg = await getString('connectioncreated', 'gradereport_gradebook_xp', {
            competency: compName,
            activity: actName
        });
        ToastService.success(msg);
    }

    static async connectionDeleted(compName, actName) {
        const msg = await getString('connectiondeleted', 'gradereport_gradebook_xp', {
            competency: compName,
            activity: actName
        });
        ToastService.success(msg);
    }

    static async connectionCreateFailed() {
        const msg = await getString('connectioncreatefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    static async connectionDeleteFailed() {
        const msg = await getString('connectiondeletefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    // Relation-specific toasts
    static async relationCreated() {
        const msg = await getString('relationcreated', 'gradereport_gradebook_xp');
        ToastService.success(msg);
    }

    static async relationDeleted() {
        const msg = await getString('relationdeleted', 'gradereport_gradebook_xp');
        ToastService.success(msg);
    }

    static async relationCreateFailed() {
        const msg = await getString('relationcreatefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }

    static async relationDeleteFailed() {
        const msg = await getString('relationdeletefailed', 'gradereport_gradebook_xp');
        ToastService.error(msg);
    }
}