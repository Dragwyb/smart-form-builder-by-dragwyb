import React from 'react';
import { Provider } from 'react-redux';
import store from './store';
import Editor from './Editor';
import StyleLoader from './StyleLoader';

const App = () => {
    return (
        <>
            <Provider store={store}>
                <StyleLoader />
                <Editor />
            </Provider>
        </>
    );
};

export default App; 