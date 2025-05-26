import React, {useCallback, useState} from 'react';
import TextField from "@mui/material/TextField";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';
import useLesson from "../hooks/useLesson";
import {useParams} from "react-router";

//interface LessonEditProps {
///    id: string;
//} ///TODO

export default function LessonEdit(): React.ReactElement {
    const {id} = useParams();
    const [lessonName, setLessonName] = useState<string>('');
    if(id !== undefined) {
        const [lesson, isLoading, isError] = useLesson(parseInt(id));
        console.log(lesson); //TODO
    }

    return (
        <div className="lesson-edit">
            <div className="lesson-header">
                <h1>Edycja lekcji</h1>
                <TextField
                    required
                    id="outlined-required"
                    label="Nazwa lekcji"
                    value={lessonName}
                    onChange={(e) => setLessonName(e.target.value)}
                />
            </div>
            <div className="lesson-words">

            </div>
            <div className="lesson-footer">
                <Button
                    className="btn btn-primary"
                    variant="contained"
                    //onClick={handleSaveLesson}
                    endIcon={<SaveIcon />}>
                    Zapisz
                </Button>
            </div>
        </div>
    );
}
