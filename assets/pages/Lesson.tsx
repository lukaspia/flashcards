import React, {useEffect, useState} from "react";
import IconButton from "@mui/material/IconButton";
import ArrowCircleLeftIcon from '@mui/icons-material/ArrowCircleLeft';
import ArrowCircleRightIcon from '@mui/icons-material/ArrowCircleRight';
import SchoolIcon from '@mui/icons-material/School';
import VolumeUpIcon from "@mui/icons-material/VolumeUp";
import TranslateIcon from '@mui/icons-material/Translate';
import Button from "@mui/material/Button";
import RestartAltIcon from '@mui/icons-material/RestartAlt';
import KeyboardReturnIcon from '@mui/icons-material/KeyboardReturn';
import TextFieldsIcon from '@mui/icons-material/TextFields';
import ArrowLeftIcon from '@mui/icons-material/ArrowLeft';
import ArrowRightIcon from '@mui/icons-material/ArrowRight';
import QuizIcon from '@mui/icons-material/Quiz';
import SwapCallsIcon from '@mui/icons-material/SwapCalls';
import SyncAltIcon from '@mui/icons-material/SyncAlt';
import {useParams} from "react-router";
import useLesson from "../hooks/useLesson";

export default function LessonTest(): React.ReactElement {
    const {id} = useParams();
    const [lesson, isLoading, isError, setLesson] = useLesson(id ? parseInt(id) : 0);

    const [studyMode, setStudyMode] = useState('learning');
    const [translationFirst, setTranslationFirst] = useState(false);

    const [index, setIndex] = useState(0);
    const [isTranslation, setIsTranslation] = useState(false);
    const [displayWord, setDisplayWord] = useState(null);

    const handleShowWord = (direction: string) => {
        let i = index;
        if (lesson != undefined) {
            if (direction == 'prev') {
                if (translationFirst) {
                    if (isTranslation) {
                        if (index > 0) {
                            i = index - 1;
                        }
                        setIndex(i);
                    }

                    setIsTranslation(true);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].translation);
                } else {
                    if (!isTranslation) {
                        if (index > 0) {
                            i = index - 1;
                        }
                        setIndex(i);
                    }


                    setIsTranslation(false);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].basicWord);
                }
            } else {
                if (isTranslation) {
                    if (!translationFirst) {
                        if (lesson.words.length > index + 1) {
                            i = index + 1;
                        }
                    }
                    setIndex(i);
                    setIsTranslation(false);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].basicWord);
                } else {
                    if (translationFirst) {
                        if (lesson.words.length > index + 1) {
                            i = index + 1;
                        }
                    }
                    setIndex(i);
                    setIsTranslation(true);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].translation);
                }
            }
        }
    }

    const handleSwitchTranslationFirst = () => {
        translationFirst ? setTranslationFirst(false) : setTranslationFirst(true);
    }

    const handleSwitchLearningProcess = () => {
        studyMode == 'learning' ? setStudyMode('testing') : setStudyMode('learning');
    }

    useEffect(() => {
        lessonReset();
    }, [lesson, translationFirst, studyMode]);

    const lessonReset = () => {
        setIndex(0);

        if (translationFirst) {
            setIsTranslation(true);
            // @ts-ignore
            setDisplayWord(lesson?.words[0].translation);
        } else {
            setIsTranslation(false);
            // @ts-ignore
            setDisplayWord(lesson?.words[0].basicWord);
        }
    }

    return (<div>
        <div className="lesson-header">
            <IconButton>
                <KeyboardReturnIcon className="basic-icon"/>
            </IconButton>
            {index + 1} / {lesson?.words.length} + licznik prawidłowych jeśli test
        </div>
        <div className="lesson-body">
            <div>
                {// @ts-ignore
                    lesson?.words[index].image && (
                        <img src={// @ts-ignore
                            lesson?.words[index].image} alt="Word illustration" className="small-image"/>
                    )}
            </div>
            <div>
                {displayWord}
                {isTranslation && (
                    <IconButton>
                        <VolumeUpIcon className="basic-icon"/>
                    </IconButton>
                )}
            </div>
            <div>
                {// @ts-ignore
                    (isTranslation && lesson?.words[index].example != '') && (
                        <div>
                            {// @ts-ignore
                                lesson?.words[index].example}
                            <IconButton>
                                <VolumeUpIcon className="basic-icon"/>
                            </IconButton>
                        </div>
                    )}
            </div>
        </div>
        <div className="lesson-footer">
            {studyMode == 'testing' ? (
                <div>
                    {(translationFirst ? isTranslation === false : isTranslation === true) ? (
                        <div>
                            <div>
                                Znałeś odpowiedź?
                            </div>
                            <div>
                                <Button onClick={() => handleShowWord('next')}>TAK</Button>
                                <Button onClick={() => handleShowWord('next')}>NIE</Button>
                            </div>
                        </div>
                        ): (
                        <div>
                            <Button onClick={() => handleShowWord('next')}>Odpowiedź</Button>
                        </div>
                        )
                    }
                </div>
            )
            :
            (
                <div>
                    <IconButton disabled={index < 1 && isTranslation == false}>
                        <ArrowCircleLeftIcon className="basic-icon" onClick={() => handleShowWord('prev')}/>
                    </IconButton>
                    <IconButton
                        disabled={index >= ((lesson?.words?.length ?? 0) - 1) && (translationFirst ? isTranslation === false : isTranslation === true)}>
                        <ArrowCircleRightIcon className="basic-icon" onClick={() => handleShowWord('next')}/>
                    </IconButton>
                </div>
            )
            }
            <div>
                <IconButton onClick={lessonReset}>
                    <RestartAltIcon className="basic-icon"/>
                </IconButton>
                <IconButton onClick={handleSwitchLearningProcess}>
                    {studyMode == 'learning' ? <SchoolIcon className="basic-icon"/> :
                        <QuizIcon className="basic-icon"/>}
                </IconButton>
                <IconButton onClick={handleSwitchLearningProcess}>
                    {studyMode == 'learning' ? <SyncAltIcon className="basic-icon"/> :
                        <SwapCallsIcon className="basic-icon"/>}
                </IconButton>
                <IconButton onClick={handleSwitchTranslationFirst}>
                    <TextFieldsIcon className="basic-icon"/> {translationFirst ?
                    <ArrowLeftIcon className="basic-icon"/> : <ArrowRightIcon className="basic-icon"/>} <TranslateIcon
                    className="basic-icon"/>
                </IconButton>
            </div>
        </div>
    </div>);
}