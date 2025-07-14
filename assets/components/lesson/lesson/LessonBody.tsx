import React from 'react';
import { IconButton } from '@mui/material';
import VolumeUpIcon from '@mui/icons-material/VolumeUp';
import { Word } from '../../../types/word.types';

interface LessonBodyProps {
    currentWord: Word | undefined;
    displayWord: string | null;
    isTranslation: boolean;
    onReadText: (text: string, key: number, type: string) => void;
    wordIndex: number;
}

export const LessonBody: React.FC<LessonBodyProps> = ({
  currentWord,
  displayWord,
  isTranslation,
  onReadText,
  wordIndex,
}) => {
    return (
        <div className="lesson-body">
            <div className="img-continer">
                {currentWord?.image && (
                    <img src={currentWord.image} alt="Word illustration" className="medium-image" />
                )}
            </div>
            <div>
                <span className="word" style={{ color: currentWord?.color }}>{displayWord}</span>
                {isTranslation && currentWord?.translation && (
                    <IconButton onClick={() => onReadText(currentWord.translation, wordIndex, 'translation')}>
                        <VolumeUpIcon className="basic-icon" />
                    </IconButton>
                )}
            </div>
            <div>
                {(isTranslation && currentWord?.example && currentWord.example !== '') && (
                    <div>
                        {currentWord.example}
                        <IconButton onClick={() => onReadText(currentWord.example, wordIndex, 'example')}>
                            <VolumeUpIcon className="basic-icon" />
                        </IconButton>
                    </div>
                )}
            </div>
        </div>
    );
};