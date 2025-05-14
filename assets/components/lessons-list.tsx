import React from 'react';
import Button from '@mui/material/Button';
import FormDialog from './lesson-dialog';

export default function LessonsList(): React.ReactElement {
    const [open, setOpen] = React.useState(false);

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
    };

    return (
        <div className="lesson-list">
            <h1>Lista lekcji</h1>
            <Button className="btn btn-primary" variant="contained" onClick={handleClickOpen}>Dodaj lekcję</Button>
            <FormDialog open={open} handleClose={handleClose} />
        </div>
    );
};