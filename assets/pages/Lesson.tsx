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
import {useNavigate, useParams} from "react-router";
import useLesson from "../hooks/useLesson";
import shuffle from "../utils/ArrayShuffler";
import {Word} from "../components/word/Word";
import Grid from "@mui/material/Grid";
import {generatePath} from "../utils/PathUtils";
import {ROUTES} from "../constants/Routes";
import {getLessonMessage, updateLesson} from "../services/api/lessonApi";
import {Lesson} from "../components/lesson/Lesson";

export default function LessonTest(): React.ReactElement {
    const {id} = useParams();
    const navigate = useNavigate();
    const [lesson, isLoading, isError, setLesson] = useLesson(id ? parseInt(id) : 0);

    const [words, setWords] = useState<Word[]>([]);
    const [nextRoundWords, setNextRoundWords] = useState<Word[]>([]);
    const [wordsError, setWordsError] = useState<Word[]>([]);
    const [round, setRound] = useState(1);
    const [showSummary, setShowSummary] = useState(false);
    const [lessonMessage, setLessonMessage] = useState('Gratulacje!');

    const [studyMode, setStudyMode] = useState('learning');
    const [translationFirst, setTranslationFirst] = useState(false);
    const [mixingWords, setMixingWords] = useState(false);

    const [index, setIndex] = useState(0);
    const [isTranslation, setIsTranslation] = useState(false);
    const [displayWord, setDisplayWord] = useState<string|null>(null);

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
                    setDisplayWord(words[i].translation);
                } else {
                    if (!isTranslation) {
                        if (index > 0) {
                            i = index - 1;
                        }
                        setIndex(i);
                    }

                    setIsTranslation(false);
                    setDisplayWord(words[i].basicWord);
                }
            } else {
                if (isTranslation) {
                    if (!translationFirst) {
                        if (words.length > index + 1) {
                            i = index + 1;
                        }
                    }
                    setIndex(i);
                    setIsTranslation(false);
                    setDisplayWord(words[i].basicWord);
                } else {
                    if (translationFirst) {
                        if (words.length > index + 1) {
                            i = index + 1;
                        }
                    }
                    setIndex(i);
                    setIsTranslation(true);
                    setDisplayWord(words[i].translation);
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

    const handleSwitchMixingWords = () => {
        if(mixingWords) {
            if (lesson && lesson.words) {
                setWords([...lesson.words]);
            }
            setMixingWords(false);
        } else {
            shuffle(words);
            setMixingWords(true);
        }
    }

    const handleAnswer = (answer: boolean, index: number) => {
        if(!answer) {
            setNextRoundWords([...nextRoundWords, words[index]]);
            updateWordError(words[index].id);
        } else {
            updateWordError(words[index].id, false);
        }

        if(index >= ((words.length ?? 0) - 1) && (translationFirst ? isTranslation === false : isTranslation === true)) {
            setShowSummary(true);
        }

        handleShowWord('next');
    }

    const updateWordError = (wordId: number, increase: boolean = true) => {
        const updatedWords = wordsError.map((word) => {
            if (word.id === wordId) {
                if(increase) {
                    return { ...word, errors: word.errors + 1};
                } else if(word.errors > 0) {
                    return { ...word, errors: word.errors - 1};
                }
            }

            return word;
        });

        setWordsError(updatedWords);
    };

    useEffect(() => {
        getLessonMessage().then(response => {
            if (response.data.message) {
                setLessonMessage(response.data.message);
            }
        });
    }, []);

    useEffect(() => {
        if (lesson && lesson.words) {
            setWords([...lesson.words]);
            setWordsError([...lesson.words]);
        } else {
            setWords([]);
            setWordsError([]);
        }
    }, [lesson]);

    useEffect(() => {
        lessonReset();
    }, [words]);

    useEffect(() => {
        lessonReset();
    }, [translationFirst, studyMode, mixingWords]);

    const lessonReset = () => {
        setIndex(0);

        if (translationFirst) {
            setIsTranslation(true);
            setDisplayWord(words[0]?.translation);
        } else {
            setIsTranslation(false);
            setDisplayWord(words[0]?.basicWord);
        }
    }

    const nextRound = () => {
        setWords([...nextRoundWords]);
        setNextRoundWords([]);
        setShowSummary(false);
        setRound(round + 1);
    }

    const handleLessonList = () => {
        const path = generatePath(ROUTES.LESSON_PANEL);
        navigate(path);
    }

    const handleSaveLesson = () => {
        const newLesson = {
            ...lesson,
            words: wordsError,
        } as Lesson;

        setLesson(newLesson);

        updateLesson(newLesson)
            .then((result) => {
            })
            .catch((error) => {
                console.error(error);
            }).finally(() => {
            handleLessonList();
        });
    }

    return (<div>
        <div className="lesson-header">

            <Grid container spacing={2}>
                <Grid size={1}>
                    <IconButton onClick={handleLessonList}>
                        <KeyboardReturnIcon className="basic-icon"/>
                    </IconButton>
                </Grid>
                <Grid size={2}>
                    {studyMode == 'testing' && (
                        <div>
                            Nieprawidłowo {nextRoundWords.length}
                        </div>
                    )}
                </Grid>
                <Grid size={9}>
                    Słowo {index + 1} / {words.length} runda {round}
                </Grid>
            </Grid>
        </div>

        {showSummary ?
            (
                <div>
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Prawidłowo</th>
                                    <th>Nieprawidłowo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{words.length - nextRoundWords.length}</td>
                                    <td>{nextRoundWords.length}</td>
                                </tr>
                            </tbody>
                        </table>
                        {nextRoundWords.length === 0 && (
                            <div>
                                <div>
                                    {lessonMessage}
                                </div>
                                <div>
                                    <Button onClick={handleSaveLesson}>Zapisz wynik i wróć do listy lekcji</Button>
                                </div>
                            </div>
                        )}
                    </div>
                    <div>
                        {nextRoundWords.length > 0 && (<Button onClick={nextRound}>Kolejna runda</Button>)}
                    </div>
                </div>
            )
            :
            (
                <div>
                    <div className="lesson-body">
                        <div>
                            {words[index]?.image && (
                                    <img src={words[index].image} alt="Word illustration" className="small-image"/>
                                )}
                        </div>
                        <div>
                            <span style={{color: words[index]?.color}}>{displayWord}</span>
                            {isTranslation && (
                                <IconButton>
                                    <VolumeUpIcon className="basic-icon"/>
                                </IconButton>
                            )}
                        </div>
                        <div>
                            {(isTranslation && words[index].example != '') && (
                                    <div>
                                        {words[index].example}
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
                                                <Button onClick={() => handleAnswer(true, index)}>TAK</Button>
                                                <Button onClick={() => handleAnswer(false, index)}>NIE</Button>
                                            </div>
                                        </div>
                                    ) : (
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
                                        <ArrowCircleLeftIcon className="basic-icon"
                                                             onClick={() => handleShowWord('prev')}/>
                                    </IconButton>
                                    <IconButton
                                        disabled={index >= ((words.length ?? 0) - 1) && (translationFirst ? isTranslation === false : isTranslation === true)}>
                                        <ArrowCircleRightIcon className="basic-icon"
                                                              onClick={() => handleShowWord('next')}/>
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
                            <IconButton onClick={handleSwitchMixingWords}>
                                {mixingWords ? <SwapCallsIcon className="basic-icon"/> :
                                    <SyncAltIcon className="basic-icon"/>}
                            </IconButton>
                            <IconButton onClick={handleSwitchTranslationFirst}>
                                <TextFieldsIcon className="basic-icon"/> {translationFirst ?
                                <ArrowLeftIcon className="basic-icon"/> : <ArrowRightIcon className="basic-icon"/>}
                                <TranslateIcon
                                    className="basic-icon"/>
                            </IconButton>
                        </div>
                    </div>
                </div>
            )
        }
    </div>);
}