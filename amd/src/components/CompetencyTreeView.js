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
 * CompetencyTreeView component for hierarchical competency navigation.
 *
 * @module    gradereport_gb_xp_admin/components/CompetencyTreeView
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gb_xp_admin/hooks/useCompetencyStore';
import {CompetencyBreadcrumb} from 'gradereport_gb_xp_admin/components/CompetencyBreadcrumb';
import {CompetencyListItem} from 'gradereport_gb_xp_admin/components/CompetencyListItem';
import {EditCompetency} from 'gradereport_gb_xp_admin/components/EditCompetency';
import {ConfirmDialog} from 'gradereport_gb_xp_admin/components/ConfirmDialog';

/**
 * CompetencyTreeView component for hierarchical competency navigation.
 *
 * @returns {Object} React element
 */
export const CompetencyTreeView = () => {
    const {createElement, useState} = window.React;
    const {competencies, relations, deleteCompetency} = useCompetencyStore();

    const [selectedCompetency, setSelectedCompetency] = useState(null);
    const [breadcrumbPath, setBreadcrumbPath] = useState([]);
    const [isAbbreviatedPath, setIsAbbreviatedPath] = useState(false); // Track if path is abbreviated
    const [showForm, setShowForm] = useState(false);
    const [editingCompetency, setEditingCompetency] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({show: false, competencyId: null, competencyName: ''});
    const [searchTerm, setSearchTerm] = useState('');
    const [searchResults, setSearchResults] = useState([]);

    // Get root competencies (those without parents)
    const getRootCompetencies = () => {
        const parentIds = relations.map(rel => rel.childid);
        return competencies.filter(comp => !parentIds.includes(comp.id));
    };

    // Get child competencies for a given parent
    const getChildCompetencies = (parentId) => {
        const childRelations = relations.filter(rel => rel.parentid === parentId);
        return childRelations.map(rel => competencies.find(c => c.id === rel.childid)).filter(Boolean);
    };

    // Get count of children for a competency
    const getChildCount = (competencyId) => {
        return relations.filter(rel => rel.parentid === competencyId).length;
    };

    // Get competencies to display based on current selection
    const getDisplayedCompetencies = () => {
        if (selectedCompetency === null) {
            return getRootCompetencies();
        }
        return getChildCompetencies(selectedCompetency.id);
    };

    // Handle competency selection by clicking on it in the list
    const handleSelectCompetency = (competency) => {
        setSelectedCompetency(competency);
        // Add to breadcrumb path (user navigated down)
        setBreadcrumbPath([...breadcrumbPath, competency]);
        setIsAbbreviatedPath(false); // Full path is known
    };

    // Handle breadcrumb navigation (user navigating back up)
    const handleBreadcrumbNavigate = (competency) => {
        if (!competency) {
            // Navigate to root
            setSelectedCompetency(null);
            setBreadcrumbPath([]);
            setIsAbbreviatedPath(false);
        } else {
            // Navigate to a specific item in the path
            const index = breadcrumbPath.findIndex(c => c.id === competency.id);
            if (index !== -1) {
                // Truncate path to this point
                setSelectedCompetency(competency);
                setBreadcrumbPath(breadcrumbPath.slice(0, index + 1));
                setIsAbbreviatedPath(false); // Path is known after breadcrumb navigation
            }
        }
    };

    // Handle add competency
    const handleAddCompetency = () => {
        setEditingCompetency(null);
        setShowForm(true);
    };

    // Get the default parent for new competencies (current selected)
    const getDefaultParent = () => {
        return selectedCompetency;
    };

    // Handle edit competency
    const handleEditCompetency = (competency) => {
        setEditingCompetency(competency);
        setShowForm(true);
    };

    // Handle close form
    const handleCloseForm = () => {
        setShowForm(false);
        setEditingCompetency(null);
    };

    // Handle delete request
    const handleDeleteRequest = (competencyId) => {
        const competency = competencies.find(c => c.id === competencyId);
        setConfirmDialog({
            show: true,
            competencyId,
            competencyName: competency?.name || 'Unknown'
        });
    };

    // Handle confirm delete
    const handleConfirmDelete = async() => {
        try {
            await deleteCompetency(confirmDialog.competencyId);
            // If deleted competency was selected or in breadcrumb, navigate up
            if (selectedCompetency && selectedCompetency.id === confirmDialog.competencyId) {
                const parentPath = breadcrumbPath.slice(0, -1);
                const newSelected = parentPath.length > 0 ? parentPath[parentPath.length - 1] : null;
                setSelectedCompetency(newSelected);
                setBreadcrumbPath(parentPath);
            }
            setConfirmDialog({show: false, competencyId: null, competencyName: ''});
        } catch (error) {
            // Error handling is done in the store
        }
    };

    // Handle cancel delete
    const handleCancelDelete = () => {
        setConfirmDialog({show: false, competencyId: null, competencyName: ''});
    };

    // Fuzzy search function
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

    // Handle search input change
    const handleSearchChange = (value) => {
        setSearchTerm(value);

        if (value.length >= 3) {
            // Perform fuzzy search
            const results = competencies.filter(comp =>
                fuzzyMatch(comp.name, value) ||
                (comp.description && fuzzyMatch(comp.description, value))
            );
            setSearchResults(results);
        } else {
            setSearchResults([]);
        }
    };

    // Handle search result click (or any non-navigation selection like edit button click)
    const handleSearchResultClick = (competency) => {
        setSelectedCompetency(competency);
        // Show abbreviated path: Root / ... / Selected
        setBreadcrumbPath([competency]);
        setIsAbbreviatedPath(true); // Mark as abbreviated since we don't know the full path
        setSearchTerm('');
        setSearchResults([]);
    };

    const displayedCompetencies = getDisplayedCompetencies();

    // Show empty state
    if (!competencies.length) {
        return createElement('div', {}, [
            createElement('div', {key: 'empty-state', className: 'text-center py-5'}, [
                createElement('h4', {key: 'no-data', className: 'text-muted mb-3'}, 'No competencies found'),
                createElement('button', {
                    key: 'add-first',
                    type: 'button',
                    className: 'btn btn-primary',
                    onClick: handleAddCompetency
                }, 'Add Your First Competency')
            ]),
            createElement(EditCompetency, {
                key: 'edit-modal',
                show: showForm,
                competency: editingCompetency,
                onClose: handleCloseForm
            })
        ]);
    }

    return createElement('div', {}, [
        // Header with Search field only
        createElement('div', {key: 'header', className: 'mb-3'}, [
            // Search field
            createElement('div', {key: 'search', className: 'position-relative', style: {maxWidth: '400px'}}, [
                createElement('input', {
                    key: 'search-input',
                    type: 'text',
                    className: 'form-control',
                    placeholder: 'Search competencies (min 3 characters)...',
                    value: searchTerm,
                    onChange: (e) => handleSearchChange(e.target.value)
                }),
                // Search results dropdown
                searchResults.length > 0 ? createElement('div', {
                    key: 'search-results',
                    className: 'position-absolute w-100 mt-1 bg-white border rounded shadow-sm',
                    style: {maxHeight: '300px', overflowY: 'auto', zIndex: 1000}
                },
                    searchResults.map(result =>
                        createElement('div', {
                            key: result.id,
                            className: 'd-flex justify-content-between align-items-center p-2 border-bottom',
                            style: {cursor: 'pointer'},
                            onMouseEnter: (e) => {
                                e.currentTarget.style.backgroundColor = '#f8f9fa';
                            },
                            onMouseLeave: (e) => {
                                e.currentTarget.style.backgroundColor = 'white';
                            }
                        }, [
                            createElement('div', {
                                key: 'name',
                                className: 'flex-grow-1',
                                onClick: () => handleSearchResultClick(result)
                            }, [
                                createElement('div', {key: 'title', className: 'fw-bold'}, result.name),
                                result.description ? createElement('div', {
                                    key: 'desc',
                                    className: 'small text-muted'
                                }, result.description.substring(0, 60) + (result.description.length > 60 ? '...' : '')) : null
                            ]),
                            createElement('div', {key: 'actions', className: 'd-flex gap-1'}, [
                                createElement('button', {
                                    key: 'edit',
                                    type: 'button',
                                    className: 'btn btn-sm btn-outline-primary',
                                    onClick: (e) => {
                                        e.stopPropagation();
                                        handleEditCompetency(result);
                                        setSearchTerm('');
                                        setSearchResults([]);
                                    },
                                    title: 'Edit'
                                }, 'Edit'),
                                createElement('button', {
                                    key: 'delete',
                                    type: 'button',
                                    className: 'btn btn-sm btn-outline-danger',
                                    onClick: (e) => {
                                        e.stopPropagation();
                                        handleDeleteRequest(result.id);
                                        setSearchTerm('');
                                        setSearchResults([]);
                                    },
                                    title: 'Delete'
                                }, 'Delete')
                            ])
                        ])
                    )
                ) : null
            ])
        ]),

        // Breadcrumb navigation with Add, Edit and Delete buttons
        createElement('div', {key: 'breadcrumb-section', className: 'd-flex justify-content-between align-items-center mb-3'}, [
            createElement(CompetencyBreadcrumb, {
                key: 'breadcrumb',
                breadcrumbPath: breadcrumbPath,
                onNavigate: handleBreadcrumbNavigate,
                showEllipsis: isAbbreviatedPath
            }),
            // Action buttons
            createElement('div', {key: 'actions', className: 'd-flex gap-2'}, [
                createElement('button', {
                    key: 'add',
                    type: 'button',
                    className: 'btn btn-sm btn-success',
                    onClick: handleAddCompetency
                }, selectedCompetency ? 'Add Sub Competency' : 'Add Competency'),
                // Edit and delete buttons for selected competency
                selectedCompetency ? createElement('button', {
                    key: 'edit-selected',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-primary',
                    onClick: () => handleEditCompetency(selectedCompetency),
                    title: 'Edit selected competency'
                }, 'Edit') : null,
                selectedCompetency ? createElement('button', {
                    key: 'delete-selected',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-danger',
                    onClick: () => handleDeleteRequest(selectedCompetency.id),
                    title: 'Delete selected competency'
                }, 'Delete') : null
            ])
        ]),

        // Competencies list
        displayedCompetencies.length > 0 ?
            createElement('div', {key: 'list', className: 'list-group'},
                displayedCompetencies.map(competency =>
                    createElement(CompetencyListItem, {
                        key: competency.id,
                        competency: competency,
                        childCount: getChildCount(competency.id),
                        onSelect: handleSelectCompetency,
                        onEdit: handleEditCompetency,
                        onDelete: handleDeleteRequest
                    })
                )
            )
        :
            createElement('div', {key: 'no-children', className: 'alert alert-info'}, [
                createElement('p', {key: 'message', className: 'mb-0'},
                    `No child competencies found for "${selectedCompetency?.name}".`
                ),
                createElement('small', {key: 'hint', className: 'text-muted'},
                    'You can add child competencies by editing this competency.'
                )
            ]),

        // Modals
        createElement(ConfirmDialog, {
            key: 'confirm-dialog',
            show: confirmDialog.show,
            title: 'Confirm Delete',
            message: `Are you sure you want to delete the competency "${confirmDialog.competencyName}"?
            This action cannot be undone.`,
            onConfirm: handleConfirmDelete,
            onCancel: handleCancelDelete
        }),
        createElement(EditCompetency, {
            key: 'edit-modal',
            show: showForm,
            competency: editingCompetency,
            defaultParent: getDefaultParent(),
            onClose: handleCloseForm
        })
    ]);
};