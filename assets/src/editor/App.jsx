import React from 'react';
import { Provider } from 'react-redux';
import store from './store';
import Editor from './Editor';
import Notice from './components/Common/Notice';
import StyleLoader from './StyleLoader';

const App = () => {
    return (
        <>
            <Provider store={store}>
                <StyleLoader />
                <Editor />
                <Notice />
            </Provider>
        </>
    );
};

export default App; 