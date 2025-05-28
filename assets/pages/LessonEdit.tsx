import React, {useCallback, useState, useEffect} from 'react';
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
    const [lessonName, setLessonName] = useState<string>('');
    const [lesson, isLoading, isError] = useLesson(id ? parseInt(id): 0);
    const [isSaving, setIsSaving] = useState(false);
    const [words, setWords] = useState([]);

    useEffect(() => {
        if (lesson?.name) {
            setLessonName(lesson.name);
        }
    }, [lesson]);

    const handleSaveLesson = () => {
        const lessonData = new FormData();
        lessonData.append('name', lessonName);
        lessonData.append('words', JSON.stringify(words));

        console.log(words);

        setIsSaving(true);
        updateLesson(lessonData)
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
                    value={lessonName}
                    onChange={(e) => setLessonName(e.target.value)}
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
