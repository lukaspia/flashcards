import React from "react";
import LessonRow from "./LessonRow";
import {Lesson} from "./Lesson";

interface LessonListRowsProps {
    lessons: Lesson[];
    handleRemoveClickOpen: (lesson: Lesson) => void;
}

export default function LessonsListRows({lessons, handleRemoveClickOpen}: LessonListRowsProps): React.ReactElement {
    return (
        <div className="lesson-list-rows">
            <table>
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Opcje</th>
                </tr>
                </thead>
                <tbody>
                    {lessons.length > 0 ? (
                        lessons.map((lesson, key) => {
                            return (
                                <tr key={key}>
                                    <LessonRow lesson={lesson} handleRemoveClickOpen={handleRemoveClickOpen} />
                                </tr>
                            )
                        })
                    ) : (
                        <tr>
                            <td colSpan={2}>Brak dostępnych lekcji.</td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
}