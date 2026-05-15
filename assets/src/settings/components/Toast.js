import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';

const Toast = ({ message, type }) => {
    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0, y: 50 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: 50 }}
                className={`dragwyb-toast dragwyb-toast-${type}`}
            >
                <i className={`fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}`}></i>
                <span>{message}</span>
            </motion.div>
        </AnimatePresence>
    );
};

export default Toast;
