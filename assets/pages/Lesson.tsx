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
import {useParams} from "react-router";
import useLesson from "../hooks/useLesson";
import {Word} from "@/components/word/Word";

export default function LessonTest(): React.ReactElement {
    const {id} = useParams();
    const [lesson, isLoading, isError, setLesson] = useLesson(id ? parseInt(id): 0);

    const [studyMode, setStudyMode] = useState('learning');
    const [translationFirst, setTranslationFirst] = useState(false);

    const [index, setIndex] = useState(0);
    const [isTranslation, setIsTranslation] = useState(false);
    const [word, setWord] = useState<Word|null>(null);
    const [displayWord, setDisplayWord] = useState(null);


    const handleShowWord = (direction: string) => {
        let i = index;
        console.log(index);
        console.log(isTranslation);

        if(lesson != undefined) {
            if(direction == 'prev') {
                if(index > 0) {
                    i = index - 1;
                }
                setIndex(i);
                if(translationFirst) {
                    setIsTranslation(true);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].translation);
                } else {
                    setIsTranslation(false);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].basicWord);
                }
                setWord(lesson.words[i]);
            } else {
                if(isTranslation) {
                    if(lesson.words.length > index + 1) {
                        i = index + 1;
                    }
                    setIndex(i);
                    setIsTranslation(false);
                    console.log('a' + i);
                    setWord(lesson.words[i]);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].basicWord);
                } else {
                    setIsTranslation(true);
                    console.log('b' + i);
                    //setWord(lesson.words[index]);
                    // @ts-ignore
                    setDisplayWord(lesson.words[i].translation);
                }
            }
        }

        console.log(word);
    }

    const handleSwitchTranslationFirst = () => {
        translationFirst ? setTranslationFirst(false) : setTranslationFirst(true);
    }

    useEffect(() => {
       lessonReset();
    }, [lesson, translationFirst]);

    const lessonReset = () => {
        setIndex(0);
        // @ts-ignore
        setWord(lesson?.words[0]);

        if(translationFirst) {
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
                {displayWord}
                {isTranslation && (
                    <IconButton>
                        <VolumeUpIcon className="basic-icon"/>
                    </IconButton>
                )}
            </div>
            <div>
                {(isTranslation && word?.example != '') && (
                    <div>
                        {word?.example}
                        <IconButton>
                            <VolumeUpIcon className="basic-icon"/>
                        </IconButton>
                    </div>
                )}
            </div>
        </div>
        <div className="lesson-footer">
            <div>
                Jeśli w trybie testu
                <Button>TAK</Button>
                <Button>NIE</Button>
            </div>
            <div>
                Jeśli w trybie nauki
                <IconButton disabled={index < 1 && isTranslation == false}>
                    <ArrowCircleLeftIcon className="basic-icon" onClick={() => handleShowWord('prev')} />
                </IconButton>
                <IconButton disabled={index >= ((lesson?.words?.length ?? 0) - 1) && isTranslation == true}>
                    <ArrowCircleRightIcon className="basic-icon" onClick={() => handleShowWord('next')} />
                </IconButton>
            </div>
            <div>
                <IconButton onClick={lessonReset}>
                    <RestartAltIcon className="basic-icon"/>
                </IconButton>
                <IconButton>
                    <SchoolIcon className="basic-icon"/> Tryb nauki/testu
                </IconButton>
                <IconButton onClick={handleSwitchTranslationFirst}>
                    <TranslateIcon className="basic-icon" />
                </IconButton>
            </div>
        </div>
    </div>);
}