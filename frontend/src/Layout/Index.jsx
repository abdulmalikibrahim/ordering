import React, { useEffect, useState } from 'react';
import NavBar from './NavBar';
import SideBar from './SideBar';
import Footer from './Footer';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const Index = ({children,API_URL}) => {
    const [titlePage,settitlePage] = useState('Dashboard')
    const [iconPage,seticonPage] = useState('mdi mdi-home')
    const setTitleIcon = (title,icon) => {
        settitlePage(title)
        seticonPage(icon)
    }

    const navigate = useNavigate();
    const handleLogout = async () => {
        try {
            const response = await axios.post(`${API_URL}/logout`,{}, {
                withCredentials: true
            })
            if(response.status === 200){
                navigate('/')
            }
        } catch (error) {
            console.error(error)
        }
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
        } else if (pageName === 'release_so') {
            setTitleIcon('Release SO', 'mdi mdi-note-plus');
        } else if (pageName === 'delete_so') {
            setTitleIcon('Delete SO', 'mdi mdi-note-plus');
        } else if (pageName === 'reject_so') {
            setTitleIcon('Reject SO', 'mdi mdi-note-plus');
        } else if (pageName === "remain_approve"){
            setTitleIcon('SO Remain Approve', 'mdi mdi-information');
        } else if (pageName === "already_approve"){
            setTitleIcon('SO Already Approve', 'mdi mdi-check-circle');
        } else if (pageName === "need_release"){
            setTitleIcon('Remain Release', 'mdi mdi-book-open-variant');
        } else if (pageName === "remain_approve_other_shop"){
            setTitleIcon('Approval Other Shop', 'mdi mdi-information');
        } else if (pageName === "myprofile"){
            setTitleIcon('My Profile', 'mdi mdi-account');
        } else {
            setTitleIcon('Dashboard', 'mdi mdi-home');
        }
    }, [window.location.pathname]);

    return (  
    <div className="container-scroller">
        <NavBar API_URL={API_URL} handleLogout={handleLogout} />
        <div className="container-fluid page-body-wrapper">
            <SideBar handleLogout={handleLogout} />
            <div className="main-panel">
                <div className="content-wrapper">
                    <div className="page-header">
                        <h3 className="page-title">
                        <span className="page-title-icon bg-gradient-primary text-white me-2">
                            <i className={iconPage}></i>
                        </span> {titlePage}
                        </h3>
                    </div>
                    {children}
                    <Footer />
                </div>
            </div>
        </div>
    </div>
    );
}

export default Index;