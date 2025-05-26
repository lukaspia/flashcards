import React, {useCallback, useState} from 'react';
import TextField from "@mui/material/TextField";
import AddIcon from "@mui/icons-material/Add";
import Button from "@mui/material/Button";
import SaveIcon from '@mui/icons-material/Save';

export default function LessonEdit(): React.ReactElement {
    const [lessonName, setLessonName] = useState<string>('');

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
