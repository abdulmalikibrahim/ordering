import { createContext, useContext, useState } from 'react';

const GlobalContext = createContext();

export const GlobalProvider = ({ children }) => {
    const [idAccount, setIdAccount] = useState('')
    const [levelAccount, setLevelAccount] = useState('')
    const [nameAccount, setNameAccount] = useState('')
    const [deptNameAccount, setDeptNameAccount] = useState('')
    const [levelNameAccount, setLevelNameAccount] = useState('')
    const [reloadCountRemain, setReloadCountRemain] = useState(0)
    const API_URL = process.env.REACT_APP_API_URL;

    return (
        <GlobalContext.Provider
        value={{ 
            idAccount,
            setIdAccount,
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
            API_URL
        }}>
            {children}
        </GlobalContext.Provider>
    );
};

export const useGlobal = () => useContext(GlobalContext);
