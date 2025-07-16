import React from 'react';
import { IconButton, Tooltip } from '@mui/material';
import RestartAltIcon from '@mui/icons-material/RestartAlt';
import AllInclusiveIcon from '@mui/icons-material/AllInclusive';
import TipsAndUpdatesIcon from '@mui/icons-material/TipsAndUpdates';
import QuizIcon from '@mui/icons-material/Quiz';
import SchoolIcon from '@mui/icons-material/School';
import SyncAltIcon from '@mui/icons-material/SyncAlt';
import SwapCallsIcon from '@mui/icons-material/SwapCalls';
import TextFieldsIcon from '@mui/icons-material/TextFields';
import ArrowLeftIcon from '@mui/icons-material/ArrowLeft';
import ArrowRightIcon from '@mui/icons-material/ArrowRight';
import TranslateIcon from '@mui/icons-material/Translate';

interface LessonOptionsProps {
    studyMode: 'learning' | 'testing';
    translationFirst: boolean;
    hardWordsMode: boolean;
    mixingWords: boolean;
    onLessonReset: () => void;
    onSwitchHardWordsMode: () => void;
    onSwitchLearningProcess: () => void;
    onSwitchMixingWords: () => void;
    onSwitchTranslationFirst: () => void;
}

export const LessonOptions: React.FC<LessonOptionsProps> = ({
    studyMode,
    translationFirst,
    hardWordsMode,
    mixingWords,
    onLessonReset,
    onSwitchHardWordsMode,
    onSwitchLearningProcess,
    onSwitchMixingWords,
    onSwitchTranslationFirst,
}) => {
    return (
        <div className="footer-options">
            <Tooltip title="Resetuj" placement="top-start">
                <IconButton onClick={onLessonReset}>
                    <RestartAltIcon className="basic-icon" />
                </IconButton>
            </Tooltip>
            <IconButton onClick={onSwitchHardWordsMode}>
                {hardWordsMode ? <Tooltip title="Włącz wszystkie słowa" placement="top-start"><AllInclusiveIcon className="basic-icon" /></Tooltip> :
                    <Tooltip title="Włącz trudne słowa" placement="top-start"><TipsAndUpdatesIcon className="basic-icon" /></Tooltip>}
            </IconButton>
            <IconButton onClick={onSwitchLearningProcess}>
                {studyMode === 'learning' ? <Tooltip title="Tryb testu" placement="top-start"><QuizIcon className="basic-icon" /></Tooltip> :
                    <Tooltip title="Tryb nauki" placement="top-start"><SchoolIcon className="basic-icon" /></Tooltip>}
            </IconButton>
            <IconButton onClick={onSwitchMixingWords}>
                {mixingWords ? <Tooltip title="Słowa w kolejności" placement="top-start"><SyncAltIcon className="basic-icon" /></Tooltip> :
                    <Tooltip title="Mieszaj słowa" placement="top-start"><SwapCallsIcon className="basic-icon" /></Tooltip>}
            </IconButton>
            <Tooltip title="Przełącz kierunek" placement="top-start">
                <IconButton onClick={onSwitchTranslationFirst}>
                    <TextFieldsIcon className="basic-icon" /> {translationFirst ?
                    <ArrowLeftIcon className="basic-icon" /> : <ArrowRightIcon className="basic-icon" />}
                    <TranslateIcon className="basic-icon" />
                </IconButton>
            </Tooltip>
        </div>
    );
};