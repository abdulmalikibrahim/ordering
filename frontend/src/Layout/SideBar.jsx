import React from 'react';
import { Link } from 'react-router-dom';
import { useGlobal } from '../GlobalContext';
import CountRemainSO from '../Component/CountRemainSO';

const SideBar = ({handleLogout, countRemainSO}) => {
    const { 
        levelAccount,
        nameAccount,
        deptNameAccount,
        levelNameAccount
    } = useGlobal();
    const path = window.location.pathname; // "/master"
    const pageName = path.split("/")[1]; // "master"
    return (
    <nav className="sidebar sidebar-offcanvas" id="sidebar">
        <ul className="nav">
            <li className="nav-item nav-profile">
                <a href="#" className="nav-link">
                <div className="nav-profile-image">
                    <img src="assets/images/faces/face1.jpg" alt="profile" />
                    <span className="login-status online"></span>
                </div>
                <div className="nav-profile-text d-flex flex-column">
                    <span className="font-weight-bold mb-2">{nameAccount}</span>
                    <span className="text-secondary text-small">{`${deptNameAccount} ${levelNameAccount}`}</span>
                </div>
                <i className="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                </a>
            </li>
            <li className={pageName === 'home' ? "nav-item active" : "nav-item"}>
                <Link className="nav-link" to="/home">
                    <span className="menu-title">Dashboard</span>
                    <i className="mdi mdi-home menu-icon"></i>
                </Link>
            </li>
            <li className={pageName.includes('master') || pageName.includes('account') || pageName.includes('db_dept') ? "nav-item active" : "nav-item"}>
                <a className="nav-link" data-bs-toggle="collapse" href="#database" aria-expanded="false" aria-controls="database">
                    <span className="menu-title">Database</span>
                    <i className="mdi mdi-database menu-icon"></i>
                </a>
                <div className={pageName.includes('master') || pageName.includes('account') || pageName.includes('db_dept') ? "collapse show" : "collapse"} id="database">
                    <ul className="nav flex-column sub-menu">
                        <li className={pageName === 'master' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/master">
                                <span className="menu-title">Master Part</span>
                            </Link>
                        </li>
                        {
                            levelAccount === "1" &&
                            <>
                                <li className={pageName === 'account' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/account">
                                        <span className="menu-title">Account</span>
                                    </Link>
                                </li>
                                <li className={pageName === 'db_dept' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/db_dept">
                                        <span className="menu-title">Dept</span>
                                    </Link>
                                </li>
                            </>
                        }
                    </ul>
                </div>
            </li>
            {
                (levelAccount === "1" || levelAccount === "2") &&
                <>
                    <li className={
                        ["input_so", "reduce_order", "additional_order", "release_so", "delete_so"].some(keyword => pageName.includes(keyword))
                        ? "nav-item active"
                        : "nav-item"}
                    >
                        <a className="nav-link" data-bs-toggle="collapse" href="#input_so" aria-expanded="false" aria-controls="input_so">
                            <span className="menu-title">Data SO</span>
                            <i className="mdi mdi-note-plus menu-icon"></i>
                        </a>
                        <div className={
                            ["input_so", "reduce_order", "additional_order", "release_so", "delete_so"].some(keyword => pageName.includes(keyword))
                            ? "collapse show"
                            : "collapse"} 
                            id="input_so"
                        >
                            <ul className="nav flex-column sub-menu">
                                <li className={pageName === 'input_so' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/input_so">
                                        <span className="menu-title">Upload SO</span>
                                    </Link>
                                </li>
                                <li className={pageName === 'reduce_order' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/reduce_order">
                                        <span className="menu-title">Reduce Order</span>
                                    </Link>
                                </li>
                                <li className={pageName === 'additional_order' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/additional_order">
                                        <span className="menu-title">Additional Order</span>
                                    </Link>
                                </li>
                                <li className={pageName === 'release_so' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/release_so">
                                        <span className="menu-title">Release SO</span>
                                    </Link>
                                </li>
                                <li className={pageName === 'delete_so' ? "nav-item active" : "nav-item"}>
                                    <Link className="nav-link" to="/delete_so">
                                        <span className="menu-title">Delete SO</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </>
            }
            {
                (levelAccount === "3" || levelAccount === "4") &&
                <>
                    <li className={pageName === 'remain_approve' ? "nav-item active" : "nav-item"}>
                        <Link className="nav-link" to="/remain_approve">
                            <span className="menu-title">SO Remain Approve <CountRemainSO /></span>
                            <i className="mdi mdi-information menu-icon"></i>
                        </Link>
                    </li>
                    <li className={pageName === 'already_approve' ? "nav-item active" : "nav-item"}>
                        <Link className="nav-link" to="/already_approve">
                            <span className="menu-title">SO Already Approve</span>
                            <i className="mdi mdi-check-circle menu-icon"></i>
                        </Link>
                    </li>
                </>
            }
            <li className="nav-item">
                <a href='#' className="nav-link" onClick={handleLogout}>
                    <span className="menu-title">Sign Out</span>
                    <i className="mdi mdi-power menu-icon"></i>
                </a>
            </li>
        </ul>
    </nav>
    );
}

export default SideBar;