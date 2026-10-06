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
 * @module    gradereport_gradebook_xp/components/CompetencyTreeView
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gradebook_xp/hooks/useCompetencyStore';
import {useStrings} from 'gradereport_gradebook_xp/hooks/useStrings';
import {CompetencyBreadcrumb} from 'gradereport_gradebook_xp/components/CompetencyBreadcrumb';
import {CompetencyListItem} from 'gradereport_gradebook_xp/components/CompetencyListItem';
import {EditCompetency} from 'gradereport_gradebook_xp/components/EditCompetency';
import {ConfirmDialog} from 'gradereport_gradebook_xp/components/ConfirmDialog';
import {ImportCompetencies} from 'gradereport_gradebook_xp/components/ImportCompetencies';

/**
 * CompetencyTreeView component for hierarchical competency navigation.
 *
 * @returns {Object} React element
 */
export const CompetencyTreeView = () => {
    const {createElement, useState, useEffect, useRef} = window.React;
    const {competencies, relations, connections, activities, deleteCompetency} = useCompetencyStore();
    const {str} = useStrings();

    const [selectedCompetency, setSelectedCompetency] = useState(null);
    const [breadcrumbPath, setBreadcrumbPath] = useState([]);
    const [isAbbreviatedPath, setIsAbbreviatedPath] = useState(false); // Track if path is abbreviated
    const [showForm, setShowForm] = useState(false);
    const [editingCompetency, setEditingCompetency] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({show: false, competencyId: null, competencyName: ''});
    const [searchTerm, setSearchTerm] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const searchRef = useRef(null);

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

        if (value.length >= 1) {
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

    // Close search on ESC key or click outside
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && searchResults.length > 0) {
                setSearchResults([]);
            }
        };

        const handleClickOutside = (e) => {
            if (searchRef.current && !searchRef.current.contains(e.target) && searchResults.length > 0) {
                setSearchResults([]);
            }
        };

        document.addEventListener('keydown', handleEscape);
        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [searchResults]);

    // Handle export data as JSON
    const handleExportData = () => {
        const exportData = {
            competencies: competencies,
            relations: relations,
            connections: connections,
            activities: activities,
            exportDate: new Date().toISOString()
        };

        const jsonString = JSON.stringify(exportData, null, 2);
        const blob = new Blob([jsonString], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `competency-export-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    const displayedCompetencies = getDisplayedCompetencies();

    // Show empty state
    if (!competencies.length) {
        return createElement('div', {}, [
            createElement('div', {key: 'empty-state', className: 'text-center py-5'}, [
                createElement('h4', {key: 'no-data', className: 'text-muted mb-3'}, str('nocompetenciesfound')),
                createElement('div', {key: 'actions', className: 'd-flex gap-2 justify-content-center'}, [
                    createElement('button', {
                        key: 'add-first',
                        type: 'button',
                        className: 'btn btn-primary',
                        onClick: handleAddCompetency
                    }, str('addfirstcompetency')),
                    createElement(ImportCompetencies, {key: 'import'})
                ])
            ]),
            createElement(EditCompetency, {
                key: 'edit-modal',
                show: showForm,
                competency: editingCompetency,
                onClose: handleCloseForm
            })
        ]);
    }

    return createElement('div', {style: {minWidth: '48em', maxWidth: '60em', margin: '0 auto'}}, [
        // Header with title
        createElement('h2', {key: 'page-title', className: 'mb-3'}, str('managecompetencies')),

        // Search and Export row
        createElement('div', {key: 'header', className: 'mb-3 d-flex justify-content-between align-items-start'}, [
            // Search field
            createElement('div',
                {key: 'search', ref: searchRef, className: 'position-relative', style: {maxWidth: '400px', flex: '1'}},
                [
                    createElement('input', {
                        key: 'search-input',
                        type: 'text',
                        className: 'form-control',
                        placeholder: str('searchplaceholder'),
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
                                createElement('div', {key: 'actions', className: 'd-flex'}, [
                                    createElement('button', {
                                        key: 'edit',
                                        type: 'button',
                                        className: 'btn btn-sm btn-outline-primary mr-2',
                                        onClick: (e) => {
                                            e.stopPropagation();
                                            handleEditCompetency(result);
                                            setSearchTerm('');
                                            setSearchResults([]);
                                        },
                                        title: str('edit')
                                    }, createElement('i', {className: 'fa fa-pen'})),
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
                                        title: str('delete')
                                    }, createElement('i', {className: 'fa fa-trash'}))
                                ])
                            ])
                        )
                    ) : null
                ]),

            createElement('div', {key: 'transfer-actions', className: 'd-flex gap-2 ml-3'}, [
                createElement(ImportCompetencies, {key: 'import'}),
                // Export button
                createElement('button', {
                    key: 'export-button',
                    type: 'button',
                    className: 'btn btn-outline-secondary',
                    onClick: handleExportData,
                    title: str('exportalldata')
                }, [
                    createElement('i', {key: 'icon', className: 'fa fa-download mr-2'}),
                    str('exportdata')
                ])
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
            createElement('div', {key: 'actions', className: 'd-flex'}, [
                createElement('button', {
                    key: 'add',
                    type: 'button',
                    className: 'btn btn-sm btn-success mr-2',
                    onClick: handleAddCompetency
                }, selectedCompetency ? str('addsubcompetency') : str('addcompetency')),
                // Edit and delete buttons for selected competency
                selectedCompetency ? createElement('button', {
                    key: 'edit-selected',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-primary mr-2',
                    onClick: () => handleEditCompetency(selectedCompetency),
                    title: str('editselectedcompetency')
                }, createElement('i', {className: 'fa fa-pen'})) : null,
                selectedCompetency ? createElement('button', {
                    key: 'delete-selected',
                    type: 'button',
                    className: 'btn btn-sm btn-outline-danger',
                    onClick: () => handleDeleteRequest(selectedCompetency.id),
                    title: str('deleteselectedcompetency')
                }, createElement('i', {className: 'fa fa-trash'})) : null
            ])
        ]),

        // Competencies list
        displayedCompetencies.length > 0 ?
            createElement('div', {key: 'list', className: 'list-group'},
                displayedCompetencies.map(competency =>
                    createElement(CompetencyListItem, {
                        key: competency.id,
                        competency: competency,
                        onSelect: handleSelectCompetency,
                        onEdit: handleEditCompetency,
                        onDelete: handleDeleteRequest
                    })
                )
            )
        :
            createElement('div', {key: 'no-children', className: 'alert alert-info'}, [
                createElement('p', {key: 'message', className: 'mb-0'},
                    str('nochildcompetenciesfound').replace('{$a}', selectedCompetency?.name || '')
                ),
                createElement('small', {key: 'hint', className: 'text-muted'},
                    'You can add child competencies by editing this competency.'
                )
            ]),

        // Modals
        createElement(ConfirmDialog, {
            key: 'confirm-dialog',
            show: confirmDialog.show,
            title: str('confirmdelete'),
            message: str('confirmdeletemessage').replace('{$a}', confirmDialog.competencyName),
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
