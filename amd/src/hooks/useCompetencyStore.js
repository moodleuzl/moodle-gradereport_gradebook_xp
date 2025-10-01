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
 * Simple global state hook for competency data.
 *
 * @module    gradereport_gb_xp_admin/useCompetencyStore
 * @copyright 2025 INB University of Luebeck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ApiService from 'gradereport_gb_xp_admin/api_service';
import ToastService from 'gradereport_gb_xp_admin/toast_service';

const {createContext, useContext, useState, useCallback, useEffect} = window.React;

// Create Context
const CompetencyContext = createContext();

// Custom hook to use the competency store
export const useCompetencyStore = () => {
    const context = useContext(CompetencyContext);
    if (!context) {
        throw new Error('useCompetencyStore must be used within a CompetencyProvider');
    }
    return context;
};

// Provider component
export const CompetencyProvider = ({children, courseid}) => {
    // TODO: Add courseid as a fixed local variable. It is never changing but must be used in the API calls
    const [competencies, setCompetencies] = useState([]);
    const [activities, setActivities] = useState([]);
    const [connections, setConnections] = useState([]);
    const [relations, setRelations] = useState([]);
    const [loading, setLoading] = useState(true);

    // Load all data on mount
    useEffect(() => {
        const loadAllData = async () => {
            try {
                setLoading(true);
                const [competenciesData, activitiesData, connectionsData, relationsData] = await Promise.all([
                    ApiService.getCompetencies(courseid),
                    ApiService.getActivities(courseid),
                    ApiService.getConnections(courseid),
                    ApiService.getRelations(courseid)
                ]);

                setCompetencies(competenciesData);
                setActivities(activitiesData);
                setConnections(connectionsData);
                setRelations(relationsData);

                ToastService.dataLoaded();
            } catch (error) {
                ToastService.dataLoadFailed();
            } finally {
                setLoading(false);
            }
        };

        loadAllData();
    }, [courseid]);

    // Competency operations
    const createCompetency = useCallback(async (competencyData) => {
        // TODO: check for cycles in relations of competencies.
        try {
            const newCompetency = await ApiService.createCompetency({...competencyData, courseid});
            setCompetencies(prev => [...prev, newCompetency]);
            ToastService.competencyCreated(competencyData.name);
            return newCompetency;
        } catch (error) {
            ToastService.competencyCreateFailed();
            throw error;
        }
    }, [courseid]);

    const updateCompetency = useCallback(async (competencyData) => {
        // TODO: check for cycles in relations of competencies.
        try {
            const updatedCompetency = await ApiService.updateCompetency(competencyData);
            setCompetencies(prev =>
                prev.map(comp => comp.id === updatedCompetency.id ? updatedCompetency : comp)
            );
            ToastService.competencyUpdated(competencyData.name);
            return updatedCompetency;
        } catch (error) {
            ToastService.competencyUpdateFailed();
            throw error;
        }
    }, []);

    const deleteCompetency = useCallback(async (competencyId) => {
        const competency = competencies.find(c => c.id === competencyId);
        const competencyName = competency?.name || 'Unknown';

        try {
            await ApiService.deleteCompetency(competencyId);

            // Refetch relations and connections as they may have been deleted server-side
            const [relationsData, connectionsData] = await Promise.all([
                ApiService.getRelations(courseid),
                ApiService.getConnections(courseid)
            ]);

            setCompetencies(prev => prev.filter(comp => comp.id !== competencyId));
            setRelations(relationsData);
            setConnections(connectionsData);

            ToastService.competencyDeleted(competencyName);
        } catch (error) {
            ToastService.competencyDeleteFailed();
            throw error;
        }
    }, [competencies, courseid]);

    // Connection operations
    const createConnection = useCallback(async ({competencyid, activityid, level = 1}) => {
        try {
            const newConnection = await ApiService.createConnection({
                competencyid,
                activityid,
                level
            });
            setConnections(prev => [...prev, newConnection]);

            const competency = competencies.find(c => c.id === competencyid);
            const activity = activities.find(a => a.id === activityid);
            ToastService.connectionCreated(competency?.name || 'Unknown', activity?.name || 'Unknown');

            return newConnection;
        } catch (error) {
            ToastService.connectionCreateFailed();
            throw error;
        }
    }, [courseid, competencies, activities]);

    const deleteConnection = useCallback(async (connectionId) => {
        const connection = connections.find(c => c.id === connectionId);
        const competency = competencies.find(c => c.id === connection?.competencyid);
        const activity = activities.find(a => a.id === connection?.activityid);

        try {
            await ApiService.deleteConnection(connectionId);
            setConnections(prev => prev.filter(conn => conn.id !== connectionId));
            ToastService.connectionDeleted(
                competency?.name || 'Unknown',
                activity?.name || 'Unknown'
            );
        } catch (error) {
            ToastService.connectionDeleteFailed();
            throw error;
        }
    }, [connections, competencies, activities]);

    // Relation operations
    const createRelation = useCallback(async (relationData) => {
        try {
            const newRelation = await ApiService.createRelation(relationData);
            setRelations(prev => [...prev, newRelation]);
            ToastService.relationCreated();
            return newRelation;
        } catch (error) {
            ToastService.relationCreateFailed();
            throw error;
        }
    }, []);

    const deleteRelation = useCallback(async (relationId) => {
        try {
            await ApiService.deleteRelation(relationId);
            setRelations(prev => prev.filter(rel => rel.id !== relationId));
            ToastService.relationDeleted();
        } catch (error) {
            ToastService.relationDeleteFailed();
            throw error;
        }
    }, []);

    // Helper functions to get filtered data
    const getConnectionsForCompetency = useCallback((competencyId) => {
        return connections.filter(conn => conn.competencyid === competencyId);
    }, [connections]);

    const getRelationsForCompetency = useCallback((competencyId) => {
        return relations.filter(rel =>
            rel.parentid === competencyId || rel.childid === competencyId
        );
    }, [relations]);

    // Get all descendants of a competency (all children, recursively)
    const getDescendants = useCallback((competencyId) => {
        const descendants = [];
        const visited = new Set();
        const stack = [competencyId];

        while (stack.length > 0) {
            const current = stack.pop();
            // Find all direct children of current competency
            const childRelations = relations.filter(rel => rel.parentid === current);

            for (const relation of childRelations) {
                if (!visited.has(relation.childid)) {
                    visited.add(relation.childid);
                    descendants.push(relation.childid);
                    stack.push(relation.childid);
                }
            }
        }

        return descendants;
    }, [relations]);

    // Get all ancestors of a competency (all parents, recursively)
    const getAncestors = useCallback((competencyId) => {
        const ancestors = [];
        const visited = new Set();
        const stack = [competencyId];

        while (stack.length > 0) {
            const current = stack.pop();
            // Find all direct parents of current competency
            const parentRelations = relations.filter(rel => rel.childid === current);

            for (const relation of parentRelations) {
                if (!visited.has(relation.parentid)) {
                    visited.add(relation.parentid);
                    ancestors.push(relation.parentid);
                    stack.push(relation.parentid);
                }
            }
        }

        return ancestors;
    }, [relations]);

    const value = {
        // Data
        competencies,
        activities,
        connections,
        relations,
        loading,

        // Operations
        createCompetency,
        updateCompetency,
        deleteCompetency,
        createConnection,
        deleteConnection,
        createRelation,
        deleteRelation,

        // Helper functions
        getConnectionsForCompetency,
        getRelationsForCompetency,
        getDescendants,
        getAncestors
    };

    return window.React.createElement(CompetencyContext.Provider, {value}, children);
};
