import { useCallback } from 'react';
import { translateWord } from '../../services/api/wordApi';
import { Word } from '../../types/word.types';

interface UseWordTranslationProps {
    wordState: Word;
    handleUpdateWord: (updates: Partial<Word>) => void;
    sourceLanguage: string;
    targetLanguage: string;
}

interface UseWordTranslationReturn {
    handleTranslateWord: (type: 'basicWord' | 'translation') => void;
}

const getTranslationParams = (type: 'basicWord' | 'translation', sourceLang: string, targetLang: string) => {
    let translationField: keyof Word;
    let translateFrom: string;
    let translateTo: string;

    if (type === 'basicWord') {
        translationField = 'translation';
        translateFrom = sourceLang;
        translateTo = targetLang;
    } else {
        translationField = 'basicWord';
        translateFrom = targetLang;
        translateTo = sourceLang;
    }
    return { translationField, translateFrom, translateTo };
};

export const useWordTranslation = ({
    wordState,
    handleUpdateWord,
    sourceLanguage,
    targetLanguage,
}: UseWordTranslationProps): UseWordTranslationReturn => {
    const handleTranslateWord = useCallback((type: 'basicWord' | 'translation') => {
        const valueToTranslate = type === 'basicWord' ? wordState.basicWord : wordState.translation;

        if (!valueToTranslate.trim()) {
            return;
        }

        const { translationField, translateFrom, translateTo } = getTranslationParams(
            type, sourceLanguage, targetLanguage
        );

        if (wordState[translationField] && (wordState[translationField] as string).trim() !== '') {
            return;
        }

        executeTranslation(valueToTranslate, translationField, translateFrom, translateTo);
    }, [wordState, sourceLanguage, targetLanguage, handleUpdateWord]);

    const executeTranslation = useCallback((word: string, translationField: keyof Word, translateFrom: string, translateTo: string) => {
        const promptData = {
            'word': word,
            'sourceLanguage': translateFrom,
            'targetLanguage': translateTo,
        };

        translateWord(promptData).then(res => {
            const updates: Partial<Word> = {
                [translationField]: res.data.translation.translation,
            };

            if (translationField === 'translation') {
                updates.example = res.data.translation.example;
            }

            handleUpdateWord(updates);
        }).catch(err => console.error(err));
    }, [handleUpdateWord]);

    return {
        handleTranslateWord,
    };
};