import React, {useCallback, useState, useEffect} from 'react';
import Button from '@mui/material/Button';
import Pagination from '@mui/material/Pagination';
import AddLessonDialog from '../components/lesson/LessonAddDialog';
import LessonsListRows from "../components/lesson/LessonsListRows";
import AddIcon from "@mui/icons-material/Add";
import LessonRemoveDialog from "../components/lesson/LessonRemoveDialog";
import {Lesson} from '../types/lesson.types';
import useLessons from "../hooks/useLessons";
import CollapseSuccessAlert from "../components/ui/CollapseSuccessAlert";
import LoadingPreloader from "../components/ui/LoadingPreloader";

export default function LessonsList(): React.ReactElement {
    const [openAddDialog, setOpenAddDialog] = useState(false);
    const [openRemoveDialog, setOpenRemoveDialog] = useState(false);
    const [lessonToRemove, setLessonToRemove] = useState<Lesson|null>(null);
    const [openSuccessAlert, setOpenSuccessAlert] = useState(false);
    const [successAlertMessage, setSuccessAlertMessage] = useState('');
    const [lessons, currentPage, totalPages, isLoading, isError, fetchLessons] = useLessons();

    const handleOpenAddDialog = useCallback(() => {
        setOpenAddDialog(true)
    }, []);

    const handleCloseAddDialog = useCallback(() => {
        setOpenAddDialog(false)
    }, []);

    const handleOpenRemoveDialog = useCallback((lesson: Lesson) => {
        setLessonToRemove(lesson);
        setOpenRemoveDialog(true);
    }, []);

    const handleCloseRemoveDialog = useCallback(() => {
        setOpenRemoveDialog(false)
    }, []);

    const showSuccessAlert = useCallback((message: string) => {
        setSuccessAlertMessage(message);
        setOpenSuccessAlert(true);
    }, []);

    const handleCloseSuccessAlert = useCallback(() => {
        setSuccessAlertMessage('');
        setOpenSuccessAlert(false);
    }, []);

    const handlePaginationChange = useCallback((event: React.ChangeEvent<unknown>, value: number) => {
        fetchLessons(value);
    }, [fetchLessons]);

    const refreshLessons = useCallback(() => {
        fetchLessons(1);
    }, [fetchLessons]);

    return (
        <div className="lesson-list">
            <div className="lesson-list-header lesson-list-section">
                <h4>Lista lekcji</h4>

                <LoadingPreloader isLoading={isLoading} />

                <Button
                    className="btn button-primary"
                    variant="contained"
                    onClick={handleOpenAddDialog}
                    endIcon={<AddIcon />}>
                    Dodaj lekcję
                </Button>
            </div>

            <CollapseSuccessAlert
                openSuccess={openSuccessAlert}
                successMessage={successAlertMessage}
                handleCloseSuccessAlert={handleCloseSuccessAlert}
            />

            <LessonsListRows
                lessons={lessons}
                handleRemoveClickOpen={handleOpenRemoveDialog}
            />

            <div className="pagination-container lesson-list-section">
                <Pagination
                    count={totalPages}
                    page={currentPage}
                    variant="outlined"
                    shape="rounded"
                    onChange={handlePaginationChange}
                />
            </div>

            <AddLessonDialog
                open={openAddDialog}
                handleClose={handleCloseAddDialog}
                fetchLessons={refreshLessons}
                handleShowSuccessAlert={() => showSuccessAlert('Lekcja została dodana.')}
            />

            <LessonRemoveDialog
                open={openRemoveDialog}
                handleClose={handleCloseRemoveDialog}
                lesson={lessonToRemove}
                fetchLessons={refreshLessons}
                handleShowSuccessRemoveAlert={() => showSuccessAlert('Lekcja została usunięta.')}
            />
        </div>
    );
};