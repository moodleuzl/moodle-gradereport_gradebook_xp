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
 * EditCompetency component for creating/editing competencies in a modal.
 *
 * @module    gradereport_gb_xp_admin/components/EditCompetency
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useCompetencyStore} from 'gradereport_gb_xp_admin/hooks/useCompetencyStore';

/**
 * EditCompetency modal component for creating/editing competencies.
 *
 * @param {Object} props Component properties
 * @param {boolean} props.show Whether to show the modal
 * @param {Object|null} props.competency The competency to edit (null for new)
 * @param {Function} props.onClose Callback when modal should close
 * @returns {Object} React element
 */
export const EditCompetency = ({show, competency, onClose}) => {
    const {createElement, useState, useEffect} = window.React;
    const {createCompetency, updateCompetency, competencies, relations, createRelation, deleteRelation, activities, connections, createConnection, deleteConnection, getConnectionsForCompetency} = useCompetencyStore();

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        maxcomlvl: 1,
        islevelsummed: 1
    });

    const [saving, setSaving] = useState(false);
    const [showParentSelector, setShowParentSelector] = useState(false);
    const [showChildSelector, setShowChildSelector] = useState(false);
    const [showActivitySelector, setShowActivitySelector] = useState(false);
    const [selectedCompetencies, setSelectedCompetencies] = useState([]);
    const [selectedActivities, setSelectedActivities] = useState([]);

    // Update form data when competency prop changes
    useEffect(() => {
        if (show) {
            setFormData({
                name: competency?.name || '',
                description: competency?.description || '',
                maxcomlvl: competency?.maxcomlvl || 1,
                islevelsummed: competency?.islevelsummed || 1
            });
            setShowParentSelector(false);
            setShowChildSelector(false);
            setShowActivitySelector(false);
            setSelectedCompetencies([]);
            setSelectedActivities([]);
        }
    }, [show, competency]);

    // Get parent and child relationships for current competency
    const getParentRelations = () => {
        if (!competency) {
            return [];
        }
        return relations.filter(rel => rel.childid === competency.id);
    };

    const getChildRelations = () => {
        if (!competency) {
            return [];
        }
        return relations.filter(rel => rel.parentid === competency.id);
    };

    const getAvailableParents = () => {
        if (!competency) {
            return competencies.filter(c => c.id !== competency?.id);
        }
        const existingParentIds = getParentRelations().map(rel => rel.parentid);
        return competencies.filter(c => c.id !== competency.id && !existingParentIds.includes(c.id));
    };

    const getAvailableChildren = () => {
        if (!competency) {
            return competencies.filter(c => c.id !== competency?.id);
        }
        const existingChildIds = getChildRelations().map(rel => rel.childid);
        return competencies.filter(c => c.id !== competency.id && !existingChildIds.includes(c.id));
    };

    if (!show) {
        return null;
    }

    const isEditing = !!competency;
    const modalTitle = isEditing ? 'Edit Competency' : 'Create New Competency';

    const handleChange = (field, value) => {
        setFormData(prev => ({...prev, [field]: value}));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            setSaving(true);

            if (isEditing) {
                await updateCompetency({...formData, id: competency.id});
            } else {
                await createCompetency(formData);
            }

            onClose();
        } catch (error) {
            // Error handling is done in the store
        } finally {
            setSaving(false);
        }
    };

    const handleClose = () => {
        if (!saving) {
            onClose();
        }
    };

    // Relationship management handlers
    const handleAddParents = () => {
        setSelectedCompetencies([]);
        setShowParentSelector(true);
    };

    const handleAddChildren = () => {
        setSelectedCompetencies([]);
        setShowChildSelector(true);
    };

    const handleToggleSelection = (competencyId) => {
        setSelectedCompetencies(prev =>
            prev.includes(competencyId)
                ? prev.filter(id => id !== competencyId)
                : [...prev, competencyId]
        );
    };

    const handleAddRelations = async (isParent) => {
        if (!competency || selectedCompetencies.length === 0) {
            return;
        }

        try {
            for (const selectedId of selectedCompetencies) {
                const relationData = isParent
                    ? { parentid: selectedId, childid: competency.id }
                    : { parentid: competency.id, childid: selectedId };
                await createRelation(relationData);
            }
            setSelectedCompetencies([]);
            setShowParentSelector(false);
            setShowChildSelector(false);
        } catch (error) {
            // Error handling is done in the store
        }
    };

    const handleRemoveRelation = async (relationId) => {
        try {
            await deleteRelation(relationId);
        } catch (error) {
            // Error handling is done in the store
        }
    };

    const handleCancelSelection = () => {
        setSelectedCompetencies([]);
        setShowParentSelector(false);
        setShowChildSelector(false);
        setSelectedActivities([]);
        setShowActivitySelector(false);
    };

    // Activity management handlers
    const handleAddActivities = () => {
        setSelectedActivities([]);
        setShowActivitySelector(true);
    };

    const handleToggleActivitySelection = (activityId) => {
        setSelectedActivities(prev =>
            prev.includes(activityId)
                ? prev.filter(id => id !== activityId)
                : [...prev, activityId]
        );
    };

    const handleAddConnections = async () => {
        if (!competency || selectedActivities.length === 0) {
            return;
        }

        try {
            for (const activityId of selectedActivities) {
                await createConnection({
                    competencyid: competency.id,
                    activityid: activityId
                });
            }
            setSelectedActivities([]);
            setShowActivitySelector(false);
        } catch (error) {
            // Error handling is done in the store
        }
    };

    const handleRemoveConnection = async (connectionId) => {
        try {
            await deleteConnection(connectionId);
        } catch (error) {
            // Error handling is done in the store
        }
    };

    // Get activities and connections for current competency
    const getCompetencyConnections = () => {
        if (!competency) return [];
        return getConnectionsForCompetency(competency.id);
    };

    const getAvailableActivities = () => {
        const connectedActivityIds = getCompetencyConnections().map(conn => conn.activityid);
        return activities.filter(activity => !connectedActivityIds.includes(activity.id));
    };

    return createElement('div', {
        className: 'modal fade show',
        style: {display: 'block', backgroundColor: 'rgba(0,0,0,0.5)'}
    }, [
        createElement('div', {key: 'modal-dialog', className: 'modal-dialog modal-lg'}, [
            createElement('div', {key: 'modal-content', className: 'modal-content'}, [
                // Modal Header
                createElement('div', {key: 'modal-header', className: 'modal-header'}, [
                    createElement('h5', {key: 'modal-title', className: 'modal-title'}, modalTitle),
                    createElement('button', {
                        key: 'close-button',
                        type: 'button',
                        className: 'btn-close',
                        onClick: handleClose,
                        disabled: saving
                    })
                ]),

                // Modal Body with Form
                createElement('form', {key: 'modal-body', onSubmit: handleSubmit}, [
                    createElement('div', {key: 'form-body', className: 'modal-body'}, [
                        createElement('div', {key: 'row', className: 'row'}, [
                            // Left Column - Basic Form Fields
                            createElement('div', {key: 'left-col', className: 'col-md-6'}, [
                                // Name Field
                                createElement('div', {key: 'name-field', className: 'mb-3'}, [
                                    createElement('label', {key: 'name-label', className: 'form-label'}, 'Name *'),
                                    createElement('input', {
                                        key: 'name-input',
                                        type: 'text',
                                        className: 'form-control',
                                        value: formData.name,
                                        onChange: (e) => handleChange('name', e.target.value),
                                        required: true,
                                        disabled: saving
                                    })
                                ]),

                                // Description Field
                                createElement('div', {key: 'description-field', className: 'mb-3'}, [
                                    createElement('label', {key: 'description-label', className: 'form-label'}, 'Description'),
                                    createElement('textarea', {
                                        key: 'description-input',
                                        className: 'form-control',
                                        rows: 3,
                                        value: formData.description,
                                        onChange: (e) => handleChange('description', e.target.value),
                                        disabled: saving
                                    })
                                ]),

                                // Maximum Level Field
                                createElement('div', {key: 'maxlevel-field', className: 'mb-3'}, [
                                    createElement('label', {key: 'maxlevel-label', className: 'form-label'}, 'Maximum Level *'),
                                    createElement('input', {
                                        key: 'maxlevel-input',
                                        type: 'number',
                                        className: 'form-control',
                                        min: 1,
                                        value: formData.maxcomlvl,
                                        onChange: (e) => handleChange('maxcomlvl', parseInt(e.target.value) || 1),
                                        required: true,
                                        disabled: saving
                                    })
                                ]),

                                // Is Level Summed Checkbox
                                createElement('div', {key: 'levelsummed-field', className: 'mb-3'}, [
                                    createElement('div', {key: 'checkbox-wrapper', className: 'form-check'}, [
                                        createElement('input', {
                                            key: 'levelsummed-input',
                                            type: 'checkbox',
                                            className: 'form-check-input',
                                            id: 'islevelsummed',
                                            checked: formData.islevelsummed === 1,
                                            onChange: (e) => handleChange('islevelsummed', e.target.checked ? 1 : 0),
                                            disabled: saving
                                        }),
                                        createElement('label', {
                                            key: 'levelsummed-label',
                                            className: 'form-check-label',
                                            htmlFor: 'islevelsummed'
                                        }, 'Is Level Summed')
                                    ])
                                ])
                            ]),

                            // Right Column - Relationship Management
                            isEditing ? createElement('div', {key: 'right-col', className: 'col-md-6'}, [
                                // Parent Competencies Section
                                createElement('div', {key: 'parents-section', className: 'mb-4'}, [
                                    createElement('div', {key: 'parents-header', className: 'd-flex justify-content-between align-items-center mb-2'}, [
                                        createElement('h6', {key: 'parents-title', className: 'mb-0'}, 'Parent Competencies'),
                                        createElement('button', {
                                            key: 'add-parents',
                                            type: 'button',
                                            className: 'btn btn-sm btn-outline-primary',
                                            onClick: handleAddParents,
                                            disabled: saving
                                        }, 'Add Parents')
                                    ]),

                                    // Parent List or Selection Interface
                                    showParentSelector ?
                                        createElement('div', {key: 'parent-selector', className: 'border rounded p-2'}, [
                                            createElement('div', {key: 'selector-header', className: 'mb-2'}, [
                                                createElement('small', {key: 'selector-text', className: 'text-muted'}, 'Select competencies to add as parents:')
                                            ]),
                                            createElement('div', {key: 'available-parents', className: 'mb-2', style: {maxHeight: '120px', overflowY: 'auto'}},
                                                getAvailableParents().map(comp =>
                                                    createElement('div', {key: comp.id, className: 'form-check'}, [
                                                        createElement('input', {
                                                            key: 'checkbox',
                                                            type: 'checkbox',
                                                            className: 'form-check-input',
                                                            id: `parent-${comp.id}`,
                                                            checked: selectedCompetencies.includes(comp.id),
                                                            onChange: () => handleToggleSelection(comp.id)
                                                        }),
                                                        createElement('label', {
                                                            key: 'label',
                                                            className: 'form-check-label',
                                                            htmlFor: `parent-${comp.id}`
                                                        }, comp.name)
                                                    ])
                                                )
                                            ),
                                            createElement('div', {key: 'selector-buttons', className: 'd-flex gap-2'}, [
                                                createElement('button', {
                                                    key: 'add',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-success',
                                                    onClick: () => handleAddRelations(true),
                                                    disabled: selectedCompetencies.length === 0
                                                }, 'Add Selected'),
                                                createElement('button', {
                                                    key: 'cancel',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-secondary',
                                                    onClick: handleCancelSelection
                                                }, 'Cancel')
                                            ])
                                        ])
                                    :
                                        createElement('div', {key: 'parents-list', className: 'border rounded p-2', style: {minHeight: '60px', maxHeight: '120px', overflowY: 'auto'}},
                                            getParentRelations().length > 0 ?
                                                getParentRelations().map(relation => {
                                                    const parent = competencies.find(c => c.id === relation.parentid);
                                                    return createElement('div', {key: relation.id, className: 'd-flex justify-content-between align-items-center py-1'}, [
                                                        createElement('span', {key: 'name', className: 'small'}, parent?.name || 'Unknown'),
                                                        createElement('button', {
                                                            key: 'remove',
                                                            type: 'button',
                                                            className: 'btn btn-sm btn-outline-danger',
                                                            onClick: () => handleRemoveRelation(relation.id)
                                                        }, '×')
                                                    ]);
                                                })
                                            :
                                                createElement('div', {key: 'no-parents', className: 'text-muted small text-center py-2'}, 'No parent competencies')
                                        )
                                ]),

                                // Child Competencies Section
                                createElement('div', {key: 'children-section', className: 'mb-3'}, [
                                    createElement('div', {key: 'children-header', className: 'd-flex justify-content-between align-items-center mb-2'}, [
                                        createElement('h6', {key: 'children-title', className: 'mb-0'}, 'Child Competencies'),
                                        createElement('button', {
                                            key: 'add-children',
                                            type: 'button',
                                            className: 'btn btn-sm btn-outline-primary',
                                            onClick: handleAddChildren,
                                            disabled: saving
                                        }, 'Add Children')
                                    ]),

                                    // Child List or Selection Interface
                                    showChildSelector ?
                                        createElement('div', {key: 'child-selector', className: 'border rounded p-2'}, [
                                            createElement('div', {key: 'selector-header', className: 'mb-2'}, [
                                                createElement('small', {key: 'selector-text', className: 'text-muted'}, 'Select competencies to add as children:')
                                            ]),
                                            createElement('div', {key: 'available-children', className: 'mb-2', style: {maxHeight: '120px', overflowY: 'auto'}},
                                                getAvailableChildren().map(comp =>
                                                    createElement('div', {key: comp.id, className: 'form-check'}, [
                                                        createElement('input', {
                                                            key: 'checkbox',
                                                            type: 'checkbox',
                                                            className: 'form-check-input',
                                                            id: `child-${comp.id}`,
                                                            checked: selectedCompetencies.includes(comp.id),
                                                            onChange: () => handleToggleSelection(comp.id)
                                                        }),
                                                        createElement('label', {
                                                            key: 'label',
                                                            className: 'form-check-label',
                                                            htmlFor: `child-${comp.id}`
                                                        }, comp.name)
                                                    ])
                                                )
                                            ),
                                            createElement('div', {key: 'selector-buttons', className: 'd-flex gap-2'}, [
                                                createElement('button', {
                                                    key: 'add',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-success',
                                                    onClick: () => handleAddRelations(false),
                                                    disabled: selectedCompetencies.length === 0
                                                }, 'Add Selected'),
                                                createElement('button', {
                                                    key: 'cancel',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-secondary',
                                                    onClick: handleCancelSelection
                                                }, 'Cancel')
                                            ])
                                        ])
                                    :
                                        createElement('div', {key: 'children-list', className: 'border rounded p-2', style: {minHeight: '60px', maxHeight: '120px', overflowY: 'auto'}},
                                            getChildRelations().length > 0 ?
                                                getChildRelations().map(relation => {
                                                    const child = competencies.find(c => c.id === relation.childid);
                                                    return createElement('div', {key: relation.id, className: 'd-flex justify-content-between align-items-center py-1'}, [
                                                        createElement('span', {key: 'name', className: 'small'}, child?.name || 'Unknown'),
                                                        createElement('button', {
                                                            key: 'remove',
                                                            type: 'button',
                                                            className: 'btn btn-sm btn-outline-danger',
                                                            onClick: () => handleRemoveRelation(relation.id)
                                                        }, '×')
                                                    ]);
                                                })
                                            :
                                                createElement('div', {key: 'no-children', className: 'text-muted small text-center py-2'}, 'No child competencies')
                                        )
                                ]),

                                // Connected Activities Section
                                createElement('div', {key: 'activities-section', className: 'mb-3'}, [
                                    createElement('div', {key: 'activities-header', className: 'd-flex justify-content-between align-items-center mb-2'}, [
                                        createElement('h6', {key: 'activities-title', className: 'mb-0'}, 'Connected Activities'),
                                        createElement('button', {
                                            key: 'add-activities',
                                            type: 'button',
                                            className: 'btn btn-sm btn-outline-success',
                                            onClick: handleAddActivities,
                                            disabled: saving
                                        }, 'Add Activities')
                                    ]),

                                    // Activity List or Selection Interface
                                    showActivitySelector ?
                                        createElement('div', {key: 'activity-selector', className: 'border rounded p-2'}, [
                                            createElement('div', {key: 'selector-header', className: 'mb-2'}, [
                                                createElement('small', {key: 'selector-text', className: 'text-muted'}, 'Select activities to connect:')
                                            ]),
                                            createElement('div', {key: 'available-activities', className: 'mb-2', style: {maxHeight: '120px', overflowY: 'auto'}},
                                                getAvailableActivities().map(activity =>
                                                    createElement('div', {key: activity.id, className: 'form-check'}, [
                                                        createElement('input', {
                                                            key: 'checkbox',
                                                            type: 'checkbox',
                                                            className: 'form-check-input',
                                                            id: `activity-${activity.id}`,
                                                            checked: selectedActivities.includes(activity.id),
                                                            onChange: () => handleToggleActivitySelection(activity.id)
                                                        }),
                                                        createElement('label', {
                                                            key: 'label',
                                                            className: 'form-check-label',
                                                            htmlFor: `activity-${activity.id}`
                                                        }, `${activity.name} (${activity.module})`)
                                                    ])
                                                )
                                            ),
                                            createElement('div', {key: 'selector-buttons', className: 'd-flex gap-2'}, [
                                                createElement('button', {
                                                    key: 'add',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-success',
                                                    onClick: handleAddConnections,
                                                    disabled: selectedActivities.length === 0
                                                }, 'Add Selected'),
                                                createElement('button', {
                                                    key: 'cancel',
                                                    type: 'button',
                                                    className: 'btn btn-sm btn-secondary',
                                                    onClick: handleCancelSelection
                                                }, 'Cancel')
                                            ])
                                        ])
                                    :
                                        createElement('div', {key: 'activities-list', className: 'border rounded p-2', style: {minHeight: '60px', maxHeight: '120px', overflowY: 'auto'}},
                                            getCompetencyConnections().length > 0 ?
                                                getCompetencyConnections().map(connection => {
                                                    const activity = activities.find(a => a.id === connection.activityid);
                                                    return createElement('div', {key: connection.id, className: 'd-flex justify-content-between align-items-center py-1'}, [
                                                        createElement('span', {key: 'name', className: 'small'}, `${activity?.name || 'Unknown'} (${activity?.module || 'Unknown'})`),
                                                        createElement('button', {
                                                            key: 'remove',
                                                            type: 'button',
                                                            className: 'btn btn-sm btn-outline-danger',
                                                            onClick: () => handleRemoveConnection(connection.id)
                                                        }, '×')
                                                    ]);
                                                })
                                            :
                                                createElement('div', {key: 'no-activities', className: 'text-muted small text-center py-2'}, 'No connected activities')
                                        )
                                ])
                            ]) : null
                        ])
                    ]),

                    // Modal Footer with Buttons
                    createElement('div', {key: 'modal-footer', className: 'modal-footer'}, [
                        createElement('button', {
                            key: 'cancel',
                            type: 'button',
                            className: 'btn btn-secondary',
                            onClick: handleClose,
                            disabled: saving
                        }, 'Cancel'),
                        createElement('button', {
                            key: 'save',
                            type: 'submit',
                            className: 'btn btn-primary',
                            disabled: saving
                        }, saving ? 'Saving...' : (isEditing ? 'Update' : 'Create'))
                    ])
                ])
            ])
        ])
    ]);
};