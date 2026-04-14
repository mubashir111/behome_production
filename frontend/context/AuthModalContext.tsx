'use client';

import { createContext, useContext, useState, useCallback } from 'react';

interface AuthModalContextValue {
    openAuthModal: (pendingAction?: () => void) => void;
    closeAuthModal: () => void;
    isOpen: boolean;
    pendingAction: (() => void) | null;
}

const AuthModalContext = createContext<AuthModalContextValue>({
    openAuthModal: () => {},
    closeAuthModal: () => {},
    isOpen: false,
    pendingAction: null,
});

export function AuthModalProvider({ children }: { children: React.ReactNode }) {
    const [isOpen, setIsOpen] = useState(false);
    const [pendingAction, setPendingAction] = useState<(() => void) | null>(null);

    const openAuthModal = useCallback((action?: () => void) => {
        setPendingAction(action ? () => action : null);
        setIsOpen(true);
    }, []);

    const closeAuthModal = useCallback(() => {
        setIsOpen(false);
        setPendingAction(null);
    }, []);

    return (
        <AuthModalContext.Provider value={{ openAuthModal, closeAuthModal, isOpen, pendingAction }}>
            {children}
        </AuthModalContext.Provider>
    );
}

export function useAuthModal() {
    return useContext(AuthModalContext);
}
