import React, {useCallback, useState} from 'react';
import TextField from "@mui/material/TextField";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';
import useLesson from "../hooks/useLesson";
import {useParams} from "react-router";
import LoadingPreloader from "../components/ui/LoadingPreloader";
import Words from "../components/word/Words";
import {updateLesson} from "../services/api/lessonApi";

export default function LessonEdit(): React.ReactElement {
    const {id} = useParams();
    const [lesson, isLoading, isError, setLesson] = useLesson(id ? parseInt(id): 0);
    const [isSaving, setIsSaving] = useState(false);
    const [words, setWords] = useState([]);

    const handleSetLessonName = (name: string) => {
        if(lesson != undefined) {
            setLesson({...lesson, name: name});
        }
    }

    const handleSaveLesson = () => {
        console.log(lesson);

        setIsSaving(true);
        if(lesson != undefined) {
            updateLesson(lesson)
                .then((response) => {
                    console.log(response);
                    //handleShowSuccessAlert();
                })
                .catch((error) => {
                    console.error(error);
                }).finally(() => {
                setIsSaving(false);
            });
        }
    }

    const updateWords = useCallback((words: any) => {
        setWords(words);
    }, []);

    return (
        <div className="lesson-edit">
            <div className="lesson-header">
                <h1>Edycja lekcji</h1>

                <LoadingPreloader isLoading={isLoading} />

                <TextField
                    required
                    id="outlined-required"
                    label="Nazwa lekcji"
                    value={lesson?.name || ''}
                    onChange={(e) => handleSetLessonName(e.target.value)}
                />
            </div>
            <div className="lesson-words-wrapper">
                <Words updateWords={updateWords} />
            </div>
            <div className="lesson-footer">
                <Button
                    className="btn btn-primary"
                    variant="contained"
                    onClick={handleSaveLesson}
                    endIcon={<SaveIcon />}>
                    Zapisz
                </Button>
            </div>
        </div>
    );
}
