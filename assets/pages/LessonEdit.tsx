import React, {useCallback, useEffect, useState} from 'react';
import TextField from "@mui/material/TextField";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';
import useLesson from "../hooks/useLesson";
import {useParams} from "react-router";
import LoadingPreloader from "../components/ui/LoadingPreloader";
import Words from "../components/word/Words";
import {updateLesson} from "../services/api/lessonApi";
import CollapseSuccessAlert from "../components/ui/CollapseSuccessAlert";
import WordsContext from "../services/context/WordsContext";
import {getCategories} from "../services/api/wordApi";

export default function LessonEdit(): React.ReactElement {
    const {id} = useParams();
    const [lesson, isLoading, isError, setLesson] = useLesson(id ? parseInt(id): 0);
    const [isSaving, setIsSaving] = useState(false);
    const [openSuccessAlert, setOpenSuccessAlert] = useState(false);
    const [successAlertMessage, setSuccessAlertMessage] = useState('');
    const [categories, setCategories] = useState<any[]>([]);

    const handleSetLessonName = (name: string) => {
        if(lesson != undefined) {
            setLesson({...lesson, name: name});
        }
    }

    const handleSaveLesson = () => {
        setIsSaving(true);
        if(lesson != undefined) {
            updateLesson(lesson)
                .then((result) => {
                    setLesson(result.data.lesson);
                    showSuccessAlert('Lekcja została zaktualizowana.');
                })
                .catch((error) => {
                    console.error(error);
                }).finally(() => {
                setIsSaving(false);
            });
        }
    }

    const updateWords = useCallback((words: any) => {
        if(lesson != undefined) {
            setLesson({...lesson, words: words});
        }
    }, [lesson, setLesson]);

    const showSuccessAlert = useCallback((message: string) => {
        setSuccessAlertMessage(message);
        setOpenSuccessAlert(true);
    }, []);

    const handleCloseSuccessAlert = useCallback(() => {
        setSuccessAlertMessage('');
        setOpenSuccessAlert(false);
    }, []);

    const wordsContextValue = {
        words: lesson?.words || [],
        updateWords: updateWords,
        wordsCategories: categories,
    };

    useEffect(() => {
        getCategories().then((result) => {
            setCategories(result.data);
        });
    },[])

    return (
        <div className="lesson-edit">
            <div className="lesson-header">
                <h4>Edycja lekcji</h4>

                <LoadingPreloader isLoading={isLoading} />

                <TextField
                    className="lesson-name-input"
                    required
                    id="outlined-required"
                    label="Nazwa lekcji"
                    value={lesson?.name || ''}
                    onChange={(e) => handleSetLessonName(e.target.value)}
                />
            </div>

            <CollapseSuccessAlert
                openSuccess={openSuccessAlert}
                successMessage={successAlertMessage}
                handleCloseSuccessAlert={handleCloseSuccessAlert}
            />

            <div className="lesson-words-wrapper">
                <WordsContext value={wordsContextValue} >
                    <Words />
                </WordsContext>
            </div>

            <div className="lesson-footer">
                <Button
                    className="btn button-primary"
                    variant="contained"
                    onClick={handleSaveLesson}
                    endIcon={<SaveIcon />}>
                    Zapisz
                </Button>
            </div>
        </div>
    );
}
