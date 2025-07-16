import React from "react";
import {Alert, Collapse, IconButton} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";

interface CollapseSuccessAlertProps {
    openSuccess: boolean;
    successMessage: string;
    handleCloseSuccessAlert: () => void;
}

export default function CollapseSuccessAlert({openSuccess, successMessage, handleCloseSuccessAlert}: CollapseSuccessAlertProps): React.ReactElement {
    return (<Collapse in={openSuccess}>
        <Alert
            action={
                <IconButton
                    aria-label="close"
                    color="inherit"
                    size="small"
                    onClick={handleCloseSuccessAlert}
                >
                    <CloseIcon fontSize="inherit" />
                </IconButton>
            }
            sx={{ mb: 2 }}
        >
            {successMessage}
        </Alert>
    </Collapse>)
}