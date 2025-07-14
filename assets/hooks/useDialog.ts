import { useState, useCallback } from 'react';

interface UseDialogResult<T = void> {
    isOpen: boolean;
    data: T | null;
    handleOpen: (arg?: T) => void;
    handleClose: () => void;
}

export const useDialog = <T = void>(): UseDialogResult<T> => {
    const [isOpen, setIsOpen] = useState(false);
    const [data, setData] = useState<T | null>(null);

    const handleOpen = useCallback((arg?: T) => {
        setIsOpen(true);
        if (arg !== undefined) {
            setData(arg);
        }
    }, []);

    const handleClose = useCallback(() => {
        setIsOpen(false);
        setData(null);
    }, []);

    return { isOpen, data, handleOpen, handleClose };
};