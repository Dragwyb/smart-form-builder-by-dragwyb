import React from 'react';
import { Provider } from 'react-redux';
import store from './store';
import Editor from './components/Editor';
import Notice from './components/Common/Notice';

const App = () => {
    return (
        <>
        <Provider store={store}>
            <Editor />
            <Notice />
        </Provider>
        </>
    );
};

export default App; 