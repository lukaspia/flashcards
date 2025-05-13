import React from 'react';

interface ButtonProps {
    title: string;
    type?: "primary" | "secondary" | "success" | "danger" | "warning" | "info" | "light" | "dark";
    clickHandler: () => void;
}

export default function Button({title, type, clickHandler}: ButtonProps): React.ReactElement {
    return <button className={`btn btn-${type}`} onClick={clickHandler}>{title}</button>;
}