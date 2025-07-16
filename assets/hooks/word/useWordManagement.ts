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
    handleUpdateWord: (updates: Partial<Word>) => void;
    handleRemoveWord: () => void;
    handleTranslateWord: (type: 'basicWord' | 'translation') => void;
    handleUploadImage: (files: FileList | null) => Promise<void>;
    handleRemoveWordImage: () => Promise<void>;
    handleReadText: (text: string, type: string) => void;
}

export const useWordManagement = ({ initialWord, keyId }: UseWordManagementProps): UseWordManagementReturn => {
    const { words, updateWords, wordsCategories, sourceLanguage, targetLanguage } = useContext(WordsContext);
    const [wordState, setWordState] = useState<Word>(initialWord);

    useEffect(() => {
        setWordState(initialWord);
    }, [initialWord]);

    const handleUpdateWord = useCallback((updates: Partial<Word>) => {
        const wordIndex = words.findIndex(w => w.id === initialWord.id);

        if (wordIndex !== -1) {
            const newWords = words.map((word, index) => {
                if (index === wordIndex) {
                    return { ...word, ...updates };
                }
                return word;
            });
            updateWords(newWords);
            setWordState(prev => ({ ...prev, ...updates }));
        }
    }, [words, initialWord.id, updateWords]);

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
            handleUpdateWord({ image: imageUrl ?? undefined });
        }, [handleUpdateWord]),
    });

    const { handleReadText } = useWordAudio({
        keyId: keyId,
        targetLanguage: targetLanguage,
    });

    useEffect(() => {
        const word = words.find(w => w.id === initialWord.id);

        if (word && !word.wordCategory && wordsCategories.length > 0) {
            handleUpdateWord({ wordCategory: wordsCategories[0] });
        }
    }, [words, wordsCategories, handleUpdateWord, initialWord.id]);

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