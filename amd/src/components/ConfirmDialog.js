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
 * ConfirmDialog component using Bootstrap modal.
 *
 * @module    gradereport_gb_xp_admin/components/ConfirmDialog
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useStrings} from 'gradereport_gb_xp_admin/hooks/useStrings';

/**
 * Confirm dialog component using Bootstrap modal.
 *
 * @param {Object} props Component properties
 * @param {boolean} props.show Whether to show the modal
 * @param {string} props.title Modal title
 * @param {string} props.message Modal message
 * @param {Function} props.onConfirm Callback when confirm button is clicked
 * @param {Function} props.onCancel Callback when cancel button is clicked
 * @returns {Object} React element
 */
export const ConfirmDialog = ({show, title, message, onConfirm, onCancel}) => {
    const {createElement} = window.React;
    const {str} = useStrings();

    if (!show) {
        return null;
    }

    return createElement('div', {
        className: 'modal fade show',
        style: {display: 'block', backgroundColor: 'rgba(0,0,0,0.5)'}
    }, [
        createElement('div', {key: 'modal-dialog', className: 'modal-dialog'}, [
            createElement('div', {key: 'modal-content', className: 'modal-content'}, [
                createElement('div', {key: 'modal-header', className: 'modal-header'}, [
                    createElement('h5', {key: 'modal-title', className: 'modal-title'}, title),
                    createElement('button', {
                        key: 'close-button',
                        type: 'button',
                        className: 'btn-close',
                        onClick: onCancel
                    })
                ]),
                createElement('div', {key: 'modal-body', className: 'modal-body'}, message),
                createElement('div', {key: 'modal-footer', className: 'modal-footer'}, [
                    createElement('button', {
                        key: 'cancel',
                        type: 'button',
                        className: 'btn btn-secondary',
                        onClick: onCancel
                    }, str('cancel')),
                    createElement('button', {
                        key: 'confirm',
                        type: 'button',
                        className: 'btn btn-danger',
                        onClick: onConfirm
                    }, str('delete'))
                ])
            ])
        ])
    ]);
};