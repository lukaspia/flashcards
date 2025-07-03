import { useCallback, useContext, useState, useEffect } from 'react';
import { Word } from "../../types/word.types";
import WordsContext from "../../services/context/WordsContext";
import { useWordImage } from './useWordImage';
import {useWordTranslation} from "./useWordTranslation";
import { useWordAudio } from './useWordAudio';

interface UseWordManagementProps {
    initialWord: Word;
    keyId: number;
}

interface UseWordManagementReturn {
    wordState: Word;
    handleUpdateWord: (field: keyof Word, value: any) => void;
    handleRemoveWord: () => void;
    handleTranslateWord: (type: 'basicWord' | 'translation') => void;
    handleUploadImage: (files: FileList | null) => Promise<void>; //TODO zająć sie tymi promise
    handleRemoveWordImage: () => Promise<void>;
    handleReadText: (text: string, type: string) => void;
}

export const useWordManagement = ({ initialWord, keyId }: UseWordManagementProps): UseWordManagementReturn => {
    const { words, updateWords, wordsCategories } = useContext(WordsContext);
    const [wordState, setWordState] = useState<Word>(initialWord);
    const [sourceLanguage] = useState('pl-PL');
    const [targetLanguage] = useState('en-US');

    useEffect(() => {
        setWordState(initialWord);
    }, [initialWord]);

    const handleUpdateWord = useCallback((field: keyof Word, value: any) => {
        const newWords = [...words];
        const wordIndex = newWords.findIndex(w => w.id === initialWord.id);
        if (wordIndex !== -1 && field !== 'id') {
            (newWords[wordIndex] as any)[field] = value;
            updateWords(newWords);
            setWordState(prev => ({ ...prev, [field]: value }));
        }
    }, []);

    const handleRemoveWord = useCallback(() => {
        const newWords = words.filter(w => w.id !== initialWord.id);
        updateWords(newWords);
    }, [words, initialWord.id, updateWords]);

    const { handleTranslateWord } = useWordTranslation({
        wordState,
        handleUpdateWord,
        sourceLanguage,
        targetLanguage,
    });

    const { handleUploadImage, handleRemoveWordImage } = useWordImage({
        wordId: initialWord.id,
        onImageUpdate: useCallback((imageUrl: string | null) => {
            handleUpdateWord('image', imageUrl);
        }, [handleUpdateWord]),
    });

    const { handleReadText } = useWordAudio({
        keyId: keyId,
        targetLanguage: targetLanguage,
    });

    useEffect(() => {
        if (!wordState.wordCategory?.id && wordsCategories.length > 0) {
            handleUpdateWord('wordCategory', wordsCategories[0].id);
        }
    }, [wordState.wordCategory, wordsCategories, handleUpdateWord]);

    return {
        wordState,
        handleUpdateWord,
        handleRemoveWord,
        handleTranslateWord,
        handleUploadImage,
        handleRemoveWordImage,
        handleReadText,
    };
}