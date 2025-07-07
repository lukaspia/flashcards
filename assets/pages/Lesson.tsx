import React from "react";
import {useNavigate, useParams} from "react-router";
import {generatePath} from "../utils/path-utils";
import {ROUTES} from "../constants/Routes";
import {useLessonData} from "../hooks/lesson/useLessonData";
import {useLessonNavigation} from "../hooks/lesson/useLessonNavigation";
import {useLessonModes} from "../hooks/lesson/useLessonModes";
import {useLessonTestLogic} from "../hooks/lesson/useLessonTestLogic";
import {useTextToSpeech} from "../hooks/useTextToSpeech";
import {LessonHeader} from "../components/lesson/lesson/LessonHeader";
import {LessonSummary} from "../components/lesson/lesson/LessonSummary";
import {LessonBody} from "../components/lesson/lesson/LessonBody";
import {LessonFooter} from "../components/lesson/lesson/LessonFooter";

export default function LessonTest(): React.ReactElement {
    const {id} = useParams();
    const navigate = useNavigate();
    const lessonId = id ? parseInt(id) : 0;

    const { lesson, isLoading, words, setWords, wordsError, setWordsError } = useLessonData({ lessonId });

    const {
        studyMode,
        translationFirst,
        mixingWords,
        hardWordsMode,
        handleSwitchTranslationFirst,
        handleSwitchLearningProcess,
        handleSwitchHardWordsMode,
        handleSwitchMixingWords,
    } = useLessonModes({ initialWords: lesson?.words || [], lesson, setWords });

    const {
        index,
        isTranslation,
        displayWord,
        handleShowWord,
        lessonReset,
    } = useLessonNavigation({ words, translationFirst });

    const handleLessonList = () => {
        const path = generatePath(ROUTES.LESSON_PANEL);
        navigate(path);
    }

    const {
        nextRoundWords,
        round,
        showSummary,
        lessonMessage,
        handleAnswer,
        nextRound,
        handleSaveLesson,
    } = useLessonTestLogic({
        words,
        wordsError,
        setWords,
        setWordsError,
        lesson,
        index,
        isTranslation,
        translationFirst,
        handleShowWord,
        handleLessonList,
    });

    const { slowRead, targetLanguage, handleReadText } = useTextToSpeech(); //TODO wykorzystać w edycji lekcji

    return (<div className="lesson">
        <LessonHeader
            onReturnToList={handleLessonList}
            studyMode={studyMode}
            nextRoundWordsCount={nextRoundWords.length}
            currentIndex={index}
            totalWords={words.length}
            round={round}
        />

        {showSummary ? (
            <LessonSummary
                totalWords={words.length}
                nextRoundWords={nextRoundWords}
                lessonMessage={lessonMessage}
                onSaveLesson={handleSaveLesson}
                onNextRound={nextRound}
            />
        ) : (
            <>
                <LessonBody
                    currentWord={words[index]}
                    displayWord={displayWord}
                    isTranslation={isTranslation}
                    onReadText={handleReadText}
                    wordIndex={index}
                />
                <LessonFooter
                    studyMode={studyMode}
                    translationFirst={translationFirst}
                    isTranslation={isTranslation}
                    hardWordsMode={hardWordsMode}
                    mixingWords={mixingWords}
                    currentIndex={index}
                    totalWords={words.length}
                    onHandleAnswer={handleAnswer}
                    onHandleShowWord={handleShowWord}
                    onLessonReset={lessonReset}
                    onSwitchHardWordsMode={handleSwitchHardWordsMode}
                    onSwitchLearningProcess={handleSwitchLearningProcess}
                    onSwitchMixingWords={handleSwitchMixingWords}
                    onSwitchTranslationFirst={handleSwitchTranslationFirst}
                />
            </>
        )}
    </div>);
}