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
import shuffle from "../utils/array-shuffler";
import {Word} from "../types/word.types";
import Grid from "@mui/material/Grid";
import {generatePath} from "../utils/path-utils";
import {ROUTES} from "../constants/Routes";
import {getLessonMessage, updateLesson} from "../services/api/lessonApi";
import {Lesson} from "../types/lesson.types";
import TipsAndUpdatesIcon from '@mui/icons-material/TipsAndUpdates';
import AllInclusiveIcon from '@mui/icons-material/AllInclusive';
import Tooltip from '@mui/material/Tooltip';
import {readText} from "../utils/text-reader";

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
    const [hardWordsMode, setHardWordsMode] = useState(false);

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

    const handleSwitchHardWordsMode = () => {
        if(hardWordsMode) {
            if (lesson && lesson.words) {
                setWords([...lesson.words]);
            }
            setHardWordsMode(false)
        } else {
            if (lesson && lesson.words) {
                const wordsWithError = lesson.words ? lesson.words.filter(word => word.errors > 0) : [];
                setWords([...wordsWithError]);
            }
            setHardWordsMode(true)
        }
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

    //TODO przenieść to na wspólną przestrzeń z LessonEdit
    const [slowRead, setSlowRead] = useState('');
    const [targetLanguage, setTargetLanguage] = useState('en-US');

    const handleReadText = (text: string, key: number, type: string) => {
        if(slowRead == key + type) {
            readText(text, targetLanguage, 0.7);
            setSlowRead('');
        } else {
            readText(text, targetLanguage);
            setSlowRead(key + type);
        }
    }

    return (<div className="lesson">
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
                                    <th className="answer-correct">Prawidłowo</th>
                                    <th className="answer-wrong">Nieprawidłowo</th>
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
                                <div className="section-separator">
                                    {lessonMessage}
                                </div>
                                <div  className="section-separator">
                                    <Button className="button-primary" variant="contained" onClick={handleSaveLesson}>Zapisz wynik i wróć do listy lekcji</Button>
                                </div>
                            </div>
                        )}
                    </div>
                    <div>
                        {nextRoundWords.length > 0 && (<Button className="button-primary" variant="contained" onClick={nextRound}>Kolejna runda</Button>)}
                    </div>
                </div>
            )
            :
            (
                <div>
                    <div className="lesson-body">
                        <div className="img-continer">
                            {words[index]?.image && (
                                    <img src={words[index].image} alt="Word illustration" className="medium-image"/>
                                )}
                        </div>
                        <div>
                            <span className="word" style={{color: words[index]?.color}}>{displayWord}</span>
                            {isTranslation && (
                                <IconButton>
                                    <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(words[index]?.translation, index, 'translation')}/>
                                </IconButton>
                            )}
                        </div>
                        <div>
                            {(isTranslation && words[index].example != '') && (
                                    <div>
                                        {words[index].example}
                                        <IconButton>
                                            <VolumeUpIcon className="basic-icon" onClick={() => handleReadText(words[index]?.example, index, 'example')}/>
                                        </IconButton>
                                    </div>
                                )}
                        </div>
                    </div>
                    <div className="lesson-footer">
                        {studyMode == 'testing' ? (
                                <div className="footer-pagination testing-mode">
                                    {(translationFirst ? isTranslation === false : isTranslation === true) ? (
                                        <div>
                                            <div className="footer-buttons-helper">
                                                Odpowiedź prawidłowa?
                                            </div>
                                            <div>
                                                <Button className="btn button-false button-separator" variant="contained" onClick={() => handleAnswer(false, index)}>NIE</Button>
                                                <Button className="btn button-true button-separator" variant="contained" onClick={() => handleAnswer(true, index)}>TAK</Button>
                                            </div>
                                        </div>
                                    ) : (
                                        <div>
                                            <div className="footer-buttons-helper">

                                            </div>
                                            <div>
                                                <Button className="btn button-primary" variant="contained" onClick={() => handleShowWord('next')}>Odpowiedź</Button>
                                            </div>
                                        </div>
                                    )
                                    }
                                </div>
                            )
                            :
                            (
                                <div className="footer-pagination learning-mode">
                                    <IconButton size="large" disabled={index < 1 && isTranslation == false}>
                                        <ArrowCircleLeftIcon fontSize="large" className="basic-icon"
                                                             onClick={() => handleShowWord('prev')}/>
                                    </IconButton>
                                    <IconButton size="large"
                                        disabled={index >= ((words.length ?? 0) - 1) && (translationFirst ? isTranslation === false : isTranslation === true)}>
                                        <ArrowCircleRightIcon fontSize="large" className="basic-icon"
                                                              onClick={() => handleShowWord('next')}/>
                                    </IconButton>
                                </div>
                            )
                        }
                        <div className="footer-options">
                            <Tooltip title="Resetuj" placement="top-start">
                                <IconButton onClick={lessonReset}>
                                    <RestartAltIcon className="basic-icon"/>
                                </IconButton>
                            </Tooltip>
                            <IconButton onClick={handleSwitchHardWordsMode}>
                                {hardWordsMode ? <Tooltip title="Włącz wszystkie słowa" placement="top-start"><AllInclusiveIcon className="basic-icon"/></Tooltip> :
                                    <Tooltip title="Włącz trudne słowa" placement="top-start"><TipsAndUpdatesIcon className="basic-icon"/></Tooltip>}
                            </IconButton>
                            <IconButton onClick={handleSwitchLearningProcess}>
                                {studyMode == 'learning' ? <Tooltip title="Tryb testu" placement="top-start"><QuizIcon className="basic-icon"/></Tooltip> :
                                    <Tooltip title="Tryb nauki" placement="top-start"><SchoolIcon className="basic-icon"/></Tooltip>}
                            </IconButton>
                            <IconButton onClick={handleSwitchMixingWords}>
                                {mixingWords ? <Tooltip title="Słowa w kolejności" placement="top-start"><SyncAltIcon className="basic-icon"/></Tooltip> :
                                    <Tooltip title="Mieszaj słowa" placement="top-start"><SwapCallsIcon className="basic-icon"/></Tooltip>}
                            </IconButton>
                            <Tooltip title="Przełącz kierunek" placement="top-start">
                                <IconButton onClick={handleSwitchTranslationFirst}>
                                    <TextFieldsIcon className="basic-icon"/> {translationFirst ?
                                    <ArrowLeftIcon className="basic-icon"/> : <ArrowRightIcon className="basic-icon"/>}
                                    <TranslateIcon className="basic-icon"/>
                                </IconButton>
                            </Tooltip>
                        </div>
                    </div>
                </div>
            )
        }
    </div>);
}