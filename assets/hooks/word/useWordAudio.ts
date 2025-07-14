import { useCallback, useState } from 'react';
import { readText } from '../../utils/text-reader';

interface UseWordAudioProps {
    keyId: number;
    targetLanguage: string;
}

interface UseWordAudioReturn {
    handleReadText: (text: string, type: string) => void;
}

export const useWordAudio = ({ keyId, targetLanguage }: UseWordAudioProps): UseWordAudioReturn => {
    const [slowReadIdentifier, setSlowReadIdentifier] = useState<string>('');

    const handleReadText = useCallback((text: string, type: string) => {
        if (!text.trim()) return;

        const identifier = `${keyId}-${type}`;
        if (slowReadIdentifier === identifier) {
            readText(text, targetLanguage, 0.7);
            setSlowReadIdentifier('');
        } else {
            readText(text, targetLanguage);
            setSlowReadIdentifier(identifier);
        }
    }, [keyId, slowReadIdentifier, targetLanguage]);

    return {
        handleReadText,
    };
};