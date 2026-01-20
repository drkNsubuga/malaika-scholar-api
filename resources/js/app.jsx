import './bootstrap';
import React from 'react';
import { Admin, Resource, ListGuesser, EditGuesser, ShowGuesser } from 'react-admin';
import { HydraAdmin, ResourceGuesser, hydraDataProvider, fetchHydra } from '@api-platform/admin';
import { createRoot } from 'react-dom/client';

// API Platform Admin configuration
const entrypoint = window.location.origin + '/api';

// Try API Platform Admin first, with fallback to basic React Admin
const ApiPlatformApp = () => {
    console.log('Trying API Platform Admin with entrypoint:', entrypoint);
    
    return (
        <div>
            <h2 style={{padding: '20px', background: '#f5f5f5', margin: 0}}>
                Malaika Scholarship Platform Admin
            </h2>
            <HydraAdmin 
                entrypoint={entrypoint} 
                title="Malaika Admin"
            >
                <ResourceGuesser name="users" />
                <ResourceGuesser name="opportunities" />
                <ResourceGuesser name="applications" />
                <ResourceGuesser name="countries" />
                <ResourceGuesser name="states" />
                <ResourceGuesser name="cities" />
                <ResourceGuesser name="schools" />
                <ResourceGuesser name="student_profiles" />
                <ResourceGuesser name="promotional_packages" />
                <ResourceGuesser name="homepage_contents" />
                <ResourceGuesser name="hero_spotlights" />
                <ResourceGuesser name="payments" />
                <ResourceGuesser name="notifications" />
                <ResourceGuesser name="documents" />
            </HydraAdmin>
        </div>
    );
};

// Fallback basic admin
const BasicApp = () => {
    console.log('Using basic React Admin');
    
    // Create a simple data provider for testing
    const dataProvider = {
        getList: (resource, params) => {
            const url = `${entrypoint}/${resource}`;
            return fetch(url)
                .then(response => response.json())
                .then(json => ({
                    data: json['hydra:member'] || [],
                    total: json['hydra:totalItems'] || 0,
                }));
        },
        getOne: (resource, params) => {
            const url = `${entrypoint}/${resource}/${params.id}`;
            return fetch(url)
                .then(response => response.json())
                .then(json => ({ data: json }));
        },
        getMany: () => Promise.resolve({ data: [] }),
        getManyReference: () => Promise.resolve({ data: [], total: 0 }),
        create: () => Promise.resolve({ data: { id: 1 } }),
        update: () => Promise.resolve({ data: { id: 1 } }),
        updateMany: () => Promise.resolve({ data: [] }),
        delete: () => Promise.resolve({ data: { id: 1 } }),
        deleteMany: () => Promise.resolve({ data: [] }),
    };

    return (
        <Admin dataProvider={dataProvider} title="Malaika Admin (Basic)">
            <Resource name="users" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="opportunities" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="applications" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="countries" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="schools" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="student_profiles" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="homepage_contents" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
            <Resource name="hero_spotlights" list={ListGuesser} edit={EditGuesser} show={ShowGuesser} />
        </Admin>
    );
};

const App = () => {
    const [useBasic, setUseBasic] = React.useState(false);
    
    // Add a timeout to switch to basic admin if API Platform admin doesn't load
    React.useEffect(() => {
        const timer = setTimeout(() => {
            console.log('API Platform Admin taking too long, switching to basic admin');
            setUseBasic(true);
        }, 10000); // 10 seconds timeout
        
        return () => clearTimeout(timer);
    }, []);
    
    if (useBasic) {
        return <BasicApp />;
    }
    
    return (
        <div>
            <ApiPlatformApp />
            <div style={{position: 'fixed', top: '10px', right: '10px', zIndex: 9999}}>
                <button 
                    onClick={() => setUseBasic(true)}
                    style={{
                        padding: '10px', 
                        background: '#ff4444', 
                        color: 'white', 
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    Switch to Basic Admin
                </button>
            </div>
        </div>
    );
};

// Mount the admin panel if we're on the admin route
if (document.getElementById('admin-app')) {
    console.log('Mounting admin app...');
    const container = document.getElementById('admin-app');
    const root = createRoot(container);
    
    try {
        root.render(<App />);
        console.log('Admin app mounted successfully');
    } catch (error) {
        console.error('Error mounting admin app:', error);
    }
} else {
    console.log('admin-app element not found');
}
