import React, {useState} from "react";
import LessonRow from "./lesson-row";

export default function LessonsListRows(): React.ReactElement {
    const [lessons, setLessons] = useState([]);

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
                    {lessons.map((lesson, key) => {
                        return (
                            <tr key={key}>
                                <LessonRow lesson={lesson} />
                            </tr>
                        )
                    })}
                </tbody>
            </table>
        </div>
    );
}