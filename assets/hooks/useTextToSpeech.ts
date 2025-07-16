import { useState } from 'react';
import {readText} from "../utils/text-reader";

interface UseTextToSpeechResult {
    slowRead: string;
    targetLanguage: string;
    handleReadText: (text: string, key: number, type: string) => void;
    setTargetLanguage: React.Dispatch<React.SetStateAction<string>>;
}

export const useTextToSpeech = (): UseTextToSpeechResult => {
    const [slowRead, setSlowRead] = useState('');
    const [targetLanguage, setTargetLanguage] = useState('en-US');

    const handleReadText = (text: string, key: number, type: string) => {
        if (!text) return;

        if (slowRead === key + type) {
            readText(text, targetLanguage, 0.7);
            setSlowRead('');
        } else {
            readText(text, targetLanguage);
            setSlowRead(key + type);
        }
    };

    return { slowRead, targetLanguage, handleReadText, setTargetLanguage };
};