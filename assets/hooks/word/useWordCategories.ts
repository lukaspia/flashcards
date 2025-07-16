import { useState, useEffect } from 'react';
import { getCategories } from '../../services/api/wordApi';

export const useWordCategories = () => {
    const [categories, setCategories] = useState<any[]>([]);

    useEffect(() => {
        getCategories().then((result) => {
            setCategories(result.data);
        });
    }, []);

    return categories;
};