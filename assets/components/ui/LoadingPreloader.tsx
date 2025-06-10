import CircularProgress from "@mui/material/CircularProgress";
import React from "react";

interface LoadingPreloaderProps {
    isLoading: boolean;
}

export default function LoadingPreloader({isLoading}: LoadingPreloaderProps): React.ReactElement {
  if (isLoading) {
      return (
          <div className='progress-container'>
              <CircularProgress size="30px"/>
          </div>
      );
  } else {
      return <div></div>;
  }
}