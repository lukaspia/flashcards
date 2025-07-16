import { useCallback } from 'react';
import { uploadImage, removeWordImage } from '../../services/api/api';

interface UseWordImageProps {
    wordId: number;
    onImageUpdate: (imageUrl: string | null) => void;
}

interface UseWordImageReturn {
    handleUploadImage: (files: FileList | null) => Promise<void>;
    handleRemoveWordImage: () => Promise<void>;
}

export const useWordImage = ({ wordId, onImageUpdate }: UseWordImageProps): UseWordImageReturn => {

    const handleUploadImage = useCallback(async (files: FileList | null) => {
        if (!files || files.length === 0) {
            return;
        }

        const file = files[0];
        const formData = new FormData();
        formData.append('image', file);
        formData.append('word', wordId.toString());

        uploadImage(formData).then(res => {
            onImageUpdate(res.data.image);
        });
    }, [wordId, onImageUpdate]);

    const handleRemoveWordImage = useCallback(async () => {
        removeWordImage(wordId).then(res => {
            onImageUpdate(null);
        });
    }, [wordId, onImageUpdate]);

    return {
        handleUploadImage,
        handleRemoveWordImage,
    };
}