import React, {useEffect, useState} from 'react';
import Button from '@mui/material/Button';
import AddLessonDialog from './lesson-dialog';
import LessonsListRows from "./lessons-list-rows";
import axios from "axios";

export default function LessonsList(): React.ReactElement {
    const [open, setOpen] = useState(false);
    const [lessons, setLessons] = useState([]);

    useEffect(() => {
        fetchLessons();
    }, []);

    const fetchLessons = () => {
        axios.get("/api/v1/lessons")
            .then((response) => {
                setLessons(response.data.data);
            })
            .catch((error) => {
                console.log(error);
            })
    }

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
            <AddLessonDialog open={open} handleClose={handleClose} fetchLessons={fetchLessons} />
            <LessonsListRows lessons={lessons} />
        </div>
    );
};