import React, {useState} from 'react';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import axios from "axios";

interface FormDialogProps {
    open: boolean;
    handleClose: () => void;
    fetchLessons: () => void;
}

export default function AddLessonDialog({open, handleClose, fetchLessons}: FormDialogProps): React.ReactElement {
    const [name, setName] = useState('');
    const [nameError, setNameError] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    const handleAddLesson = () => {
        if(name == '') {
            setNameError(true);
        } else {
            const formData = new FormData();
            formData.append('name', name);
            axios.post('/api/v1/lesson', formData)
                .then((response: any) => {
                    fetchLessons();
                    resetForm();
                    handleClose();
                }).catch((error: any) => {
                console.error(error);
                setIsSaving(false);
            });
        }
    };

    const resetForm = () => {
        setIsSaving(false);
        setName('');
        setNameError(false);
    };

    return (
        <React.Fragment>
            <Dialog open={open} onClose={handleClose} aria-hidden={!open}>
                <DialogTitle>Dodawanie lekcji</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                    Wpisz nazwę lekcji
                    </DialogContentText>
                    <TextField
                        autoFocus
                        required
                        margin="dense"
                        id="lesson-name"
                        name="lesson_name"
                        label="Nazwa lekcji"
                        type="text"
                        fullWidth
                        variant="standard"
                        onChange={(e) => setName(e.target.value)}
                        error={nameError}
                        helperText={nameError ? "Wprowadź nazwę lekcji" : ""}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose}>Anuluj</Button>
                    <Button disabled={isSaving} onClick={handleAddLesson} type="submit">Dodaj</Button>
                </DialogActions>
            </Dialog>
        </React.Fragment>
    );
}
