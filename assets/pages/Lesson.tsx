import React from "react";
import IconButton from "@mui/material/IconButton";
import ArrowCircleLeftIcon from '@mui/icons-material/ArrowCircleLeft';
import ArrowCircleRightIcon from '@mui/icons-material/ArrowCircleRight';
import SchoolIcon from '@mui/icons-material/School';
import VolumeUpIcon from "@mui/icons-material/VolumeUp";
import TranslateIcon from '@mui/icons-material/Translate';
import Button from "@mui/material/Button";
import RestartAltIcon from '@mui/icons-material/RestartAlt';

export default function LessonTest(): React.ReactElement {
    return (<div>
        <div className="lesson-header">
           1 / 20 + licznik prawidłowych jeśli test
        </div>
        <div className="lesson-body">
            <div>
                Słowo
                <IconButton>
                    <VolumeUpIcon className="basic-icon"/>
                </IconButton>
            </div>
            <div>
                Przykład (jeśli en)
                <IconButton>
                    <VolumeUpIcon className="basic-icon"/>
                </IconButton>
            </div>
        </div>
        <div className="lesson-footer">
            <div>
                Jeśli w trybie testu
                <Button>TAK</Button>
                <Button>NIE</Button>
            </div>
            <div>
                Jeśli w trybie nauki
                <IconButton>
                    <ArrowCircleLeftIcon className="basic-icon"/>
                </IconButton>
                <IconButton>
                    <ArrowCircleRightIcon className="basic-icon"/>
                </IconButton>
            </div>
            <div>
                <IconButton>
                    <RestartAltIcon className="basic-icon"/> Resetowanie lekcji
                </IconButton>
                <IconButton>
                    <SchoolIcon className="basic-icon"/> Tryb nauki/testu
                </IconButton>
                <IconButton>
                    <TranslateIcon className="basic-icon"/> Przełączanie PL/EN EN/PL
                </IconButton>
            </div>
        </div>
    </div>);
}