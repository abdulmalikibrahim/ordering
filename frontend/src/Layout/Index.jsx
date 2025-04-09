import React, { useEffect, useState } from 'react';
import NavBar from './NavBar';
import SideBar from './SideBar';
import Footer from './Footer';

const Index = ({children,API_URL}) => {
    const [titlePage,settitlePage] = useState('Dashboard')
    const [iconPage,seticonPage] = useState('mdi mdi-home')
    const setTitleIcon = (title,icon) => {
        console.log(title,icon)
        settitlePage(title)
        seticonPage(icon)
    }

    useEffect(() => {
        const path = window.location.pathname;
        const pageName = path.split("/")[1];
        if (pageName === 'master') {
            setTitleIcon('Database Master', 'mdi mdi-database');
        } else if (pageName === 'account') {
            setTitleIcon('Database Account', 'mdi mdi-contacts');
        } else if (pageName === 'db_dept') {
            setTitleIcon('Database Department', 'mdi mdi-domain');
        } else if (pageName === 'input_so') {
            setTitleIcon('Upload SO', 'mdi mdi-note-plus');
        } else if (pageName === 'reduce_order') {
            setTitleIcon('Reduce Order', 'mdi mdi-note-plus');
        } else if (pageName === 'additional_order') {
            setTitleIcon('Additional Order', 'mdi mdi-note-plus');
        } else {
            setTitleIcon('Dashboard', 'mdi mdi-home');
        }
    }, [window.location.pathname]);

    return (  
    <div className="container-scroller">
        <NavBar API_URL={API_URL} />
        <div className="container-fluid page-body-wrapper">
            <SideBar />
            <div className="main-panel">
                <div className="content-wrapper">
                    <div className="page-header">
                        <h3 className="page-title">
                        <span className="page-title-icon bg-gradient-primary text-white me-2">
                            <i className={iconPage}></i>
                        </span> {titlePage}
                        </h3>
                    </div>
                    {/* BODY IN HERE */}
                    {children}
                    <Footer />
                </div>
            </div>
        </div>
    </div>
    );
}

export default Index;