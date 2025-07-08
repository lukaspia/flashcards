import { useState, useCallback } from 'react';

export const useSuccessAlert = () => {
    const [openSuccessAlert, setOpenSuccessAlert] = useState(false);
    const [successAlertMessage, setSuccessAlertMessage] = useState('');

    const showSuccessAlert = useCallback((message: string) => {
        setSuccessAlertMessage(message);
        setOpenSuccessAlert(true);
    }, []);

    const handleCloseSuccessAlert = useCallback(() => {
        setSuccessAlertMessage('');
        setOpenSuccessAlert(false);
    }, []);

    return { openSuccessAlert, successAlertMessage, showSuccessAlert, handleCloseSuccessAlert };
};