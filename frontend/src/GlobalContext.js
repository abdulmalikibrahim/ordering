import { createContext, useContext, useState } from 'react';

const GlobalContext = createContext();

export const GlobalProvider = ({ children }) => {
    const [idAccount, setIdAccount] = useState('')
    const [levelAccount, setLevelAccount] = useState('')
    const [nameAccount, setNameAccount] = useState('')
    const [deptNameAccount, setDeptNameAccount] = useState('')
    const [levelNameAccount, setLevelNameAccount] = useState('')
    const [usernameAccount, setUsernameAccount] = useState('')
    const [emailAccount, setEmailAccount] = useState('')
    const [deptIdAccount, setDeptIdAccount] = useState('')
    const [reloadCountRemain, setReloadCountRemain] = useState(0)
    const API_URL = process.env.REACT_APP_API_URL;
    
    const numberFormat = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(num)
    }

    const formatDateIndo = (dateString) => {
        const date = new Date(dateString);
        const day = date.getDate().toString().padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'short' }); // 'May'
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const mins = date.getMinutes().toString().padStart(2, '0');
        const secs = date.getSeconds().toString().padStart(2, '0');
        return `${day}-${month}-${year} ${hours}:${mins}:${secs}`;
    };

    return (
        <GlobalContext.Provider
        value={{ 
            idAccount,
            setIdAccount,
            usernameAccount,
            setUsernameAccount,
            emailAccount,
            setEmailAccount,
            deptIdAccount,
            setDeptIdAccount,
            levelAccount,
            setLevelAccount,
            nameAccount,
            setNameAccount,
            deptNameAccount,
            setDeptNameAccount,
            levelNameAccount,
            setLevelNameAccount,
            reloadCountRemain,
            setReloadCountRemain,
            API_URL,
            numberFormat,
            formatDateIndo
        }}>
            {children}
        </GlobalContext.Provider>
    );
};

export const useGlobal = () => useContext(GlobalContext);
