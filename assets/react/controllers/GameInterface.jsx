// src/components/GameInterface.jsx

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { javascript } from '@codemirror/lang-javascript';
import { php } from '@codemirror/lang-php';
import { python } from '@codemirror/lang-python';

import { eclipse } from '@uiw/codemirror-theme-eclipse';
import { dracula } from '@uiw/codemirror-theme-dracula';

// --- 1. CONFIGURATION DES THÈMES ET DES PROPS ---
const IMAGE_BASE_PATH = '/assets/images/';

const THEMES = {
    php: {
        name: 'php',
        background: `${IMAGE_BASE_PATH}Capture_decran_2025-11-06_a_13.50.18.jpg`,
        extensions: [php()],
        languageName: 'php',
        codemirrorTheme: eclipse,
        enonceStyle: 'bg-yellow-50/90 text-gray-900 border-4 border-amber-900/70 shadow-[0_0_20px_rgba(160,82,45,0.7)] p-4 lg:p-6 rounded-lg font-serif italic text-base scrollbar-thumb-amber-700 scrollbar-track-yellow-100 scrollbar-thin',
        codeContainerStyle: 'bg-gray-100/90 border-4 border-amber-900/70 shadow-2xl rounded-lg',
        uiTextColor: 'text-amber-900',
        buttonStyle: 'bg-amber-800 hover:bg-amber-900 text-yellow-100 font-extrabold shadow-md shadow-amber-900/50',
    },
    javascript: {
        name: 'javascript',
        background: `${IMAGE_BASE_PATH}Capture_decran_2025-11-06_a_13.51.11.jpg`,
        extensions: [javascript({ jsx: true })],
        languageName: 'javascript',
        codemirrorTheme: dracula,
        enonceStyle: 'bg-gray-900/90 text-cyan-400 border-4 border-cyan-500 shadow-[0_0_25px_rgba(0,255,255,0.5)] p-4 lg:p-6 rounded-xl font-mono text-base scrollbar-thumb-cyan-500 scrollbar-track-gray-800 scrollbar-thin',
        codeContainerStyle: 'bg-gray-900/90 border-4 border-cyan-500 shadow-[0_0_20px_rgba(0,255,255,0.3)] rounded-xl',
        uiTextColor: 'text-cyan-400',
        buttonStyle: 'bg-cyan-600/70 hover:bg-cyan-500/80 border border-cyan-400 text-white shadow-[0_0_15px_rgba(0,255,255,0.7)]',
    },
    python: {
        name: 'python',
        background: `${IMAGE_BASE_PATH}1a6a54d6-80e0-4c19-9a21-3f060b1213e0.jpg`,
        extensions: [python()],
        languageName: 'python',
        codemirrorTheme: dracula,
        enonceStyle: 'bg-green-900/85 text-lime-300 border-4 border-lime-500 shadow-[0_0_20px_rgba(50,205,50,0.7)] p-4 lg:p-6 rounded-lg font-sans text-base scrollbar-thumb-lime-500 scrollbar-track-green-800 scrollbar-thin',
        codeContainerStyle: 'bg-green-900/80 border-4 border-lime-500 shadow-xl rounded-lg',
        uiTextColor: 'text-lime-400',
        buttonStyle: 'bg-lime-700 hover:bg-lime-800 text-white shadow-[0_0_10px_rgba(100,205,50,0.5)]',
    },
};

// Fonction utilitaire pour décoder le JSON des props Twig
const parseJsonProp = (prop) => {
    if (typeof prop === 'string') {
        try {
            const parsed = JSON.parse(prop);
            return Array.isArray(parsed) || prop.includes('tips') ? parsed || [] : parsed;
        } catch (e) {
            return prop.includes('tips') ? [] : prop; 
        }
    }
    return prop;
};

// --- 2. COMPOSANT PRINCIPAL ---

const GameInterface = ({ levelId, language, enonce, lifes, maxLifes, levelNumber, tips, xpGain }) => {
    
    // États de base
    const [code, setCode] = useState('');
    const [currentLifes, setCurrentLifes] = useState(lifes);
    const [isGameOver, setIsGameOver] = useState(false);
    const [isVictory, setIsVictory] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submissionResult, setSubmissionResult] = useState(null); 
    
    // États pour la logique des indices
    const [showHintModal, setShowHintModal] = useState(false); 
    const [hintStatusMessage, setHintStatusMessage] = useState(''); 
    const [isHintUnlocked, setIsHintUnlocked] = useState(false); 
    const [currentXpGain, setCurrentXpGain] = useState(xpGain); 

    
    // Parsing et constantes
    const parsedEnonce = useMemo(() => parseJsonProp(enonce), [enonce]);
    const normalizedLanguage = language.toLowerCase();
    const theme = THEMES[normalizedLanguage] || THEMES.javascript;
    const nextLevelPath = `/game/${normalizedLanguage}/${parseInt(levelNumber, 10) + 1}`; 
    
    const parsedTips = useMemo(() => parseJsonProp(tips), [tips]); 
    const actualHintContent = parsedTips.length > 0 && parsedTips[0].name
        ? parsedTips[0].name 
        : "Pas d'astuce disponible."; 
    
    // RÈGLES DE L'INDICE
    const PURCHASE_LIFES_THRESHOLD = 5; 
    const FREE_LIFES_THRESHOLD = 3;     
    const XP_COST_PERCENTAGE = 0.50;    
    const XP_COST = Math.floor(xpGain * XP_COST_PERCENTAGE); 
    const availableHints = isHintUnlocked ? 0 : 1;

    // Synchronisation des vies
    useEffect(() => {
        if (currentLifes <= 0 && !isVictory && !isGameOver) {
            setIsGameOver(true);
        }
    }, [currentLifes, isVictory, isGameOver]);

    useEffect(() => {
        setCurrentLifes(lifes);
        setCurrentXpGain(xpGain);
    }, [lifes, xpGain]);

    // --- Logique de l'Indice : Achat et Affichage ---
    
    const unlockHint = (isFree) => {
        if (actualHintContent === "Pas d'astuce disponible.") {
            setHintStatusMessage("Désolé, aucune astuce n'a été trouvée pour ce niveau.");
            return;
        }

        if (isHintUnlocked) return;

        if (isFree) {
            setIsHintUnlocked(true);
            setHintStatusMessage("Indice GRATUIT débloqué ! Il est maintenant affiché sous la mission.");
            return;
        }
        
        // Achat
        if (currentLifes <= PURCHASE_LIFES_THRESHOLD && currentLifes > FREE_LIFES_THRESHOLD) {
            setCurrentXpGain(currentXpGain - XP_COST); 
            setIsHintUnlocked(true);
            setHintStatusMessage(`Indice acheté ! ${XP_COST} XP déduits de la récompense finale.`);
        } else {
            setHintStatusMessage("Achat impossible : conditions de vies non remplies.");
        }
    }

    const handleHintButtonClick = () => {
        
        if (isHintUnlocked) {
            setHintStatusMessage("L'indice est déjà débloqué pour ce niveau. Voulez-vous le revoir ?");
            setShowHintModal(true);
            return;
        }

        if (actualHintContent === "Pas d'astuce disponible.") {
            setHintStatusMessage("Désolé, aucune astuce n'a été trouvée pour ce niveau. Vous ne pouvez rien débloquer.");
            setShowHintModal(true);
            return;
        }
        
        // Logique de déblocage GRATUIT (Vies <= 3)
        if (currentLifes <= FREE_LIFES_THRESHOLD) {
            setHintStatusMessage(`Félicitations ! Il vous reste ${currentLifes} vies. L'indice est GRATUIT !`);
            setShowHintModal(true);
            return;
        }
        
        // Logique d'ACHAT (Vies <= 5 et > 3)
        if (currentLifes <= PURCHASE_LIFES_THRESHOLD) {
            setHintStatusMessage(`Vous pouvez acheter l'indice pour ${XP_COST} XP (50% de la récompense).`);
            setShowHintModal(true);
            return;
        } else {
            // Bloqué (Trop de vies restantes > 5)
            setHintStatusMessage(`Veuillez attendre de descendre à ${PURCHASE_LIFES_THRESHOLD} vies ou moins pour acheter un indice.`);
            setShowHintModal(true);
        }
    };
    
    const handleModalAction = () => {
        if (isHintUnlocked) {
            setShowHintModal(false); 
            return;
        }

        if (currentLifes <= FREE_LIFES_THRESHOLD) {
            unlockHint(true); // Gratuit
        } else if (currentLifes <= PURCHASE_LIFES_THRESHOLD) {
            unlockHint(false); // Achat
        }
    };
    
    // --- Logique de Soumission (ENVOI DE L'XP AJUSTÉE) ---
    const handleSubmit = useCallback(async () => {
        if (isSubmitting || isGameOver || isVictory) return;

        setIsSubmitting(true);
        setSubmissionResult(null);

        try {
            const response = await fetch('/api/game/execute', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    language: theme.languageName,
                    code: code,
                    level_id: levelId, 
                    current_lifes: currentLifes,
                    experience: currentXpGain, // ENVOI DE L'XP AJUSTÉE
                }),
            });

            const result = await response.json();
            setSubmissionResult(result);

            if (result.newLifes !== undefined) {
                setCurrentLifes(result.newLifes);
            }
            
            if (result.isSuccess) {
                if (result.experienceGained !== undefined) {
                    setCurrentXpGain(result.experienceGained); 
                }
                setIsVictory(true); 
            }
            
        } catch (error) {
            console.error('Erreur lors de la soumission du code:', error);
            setSubmissionResult({ isSuccess: false, error: 'Erreur de connexion ou API.' });
        } finally {
            setIsSubmitting(false);
        }
    }, [code, theme.languageName, levelId, currentLifes, isSubmitting, isGameOver, isVictory, currentXpGain]);

    // --- Rendu des Vies (SEPARATION VISUELLE) ---
    const renderLifes = () => (
        <div className="flex items-center space-x-1 p-1 rounded-full bg-red-900/50 shadow-xl border border-red-700">
            {[...Array(maxLifes)].map((_, index) => (
                <span 
                    key={`life-${index}`} 
                    className={`text-2xl transition-all duration-500 ${index < currentLifes ? 'text-red-400 opacity-100' : 'text-gray-600 opacity-50'}`} 
                    role="img" 
                    aria-label={index < currentLifes ? "Vie" : "Vie perdue"}
                >
                    {index < currentLifes ? '❤️' : '💔'}
                </span>
            ))}
            <span className={`text-xl font-bold ml-2 self-center ${theme.uiTextColor}`}>
                {currentLifes}
            </span>
        </div>
    );
    
    // --- Composant Modal d'Indice (Pop-up d'Achat/Prévention) ---
    const HintModal = () => {
        const hasTips = actualHintContent !== "Pas d'astuce disponible.";
        const isFree = hasTips && currentLifes <= FREE_LIFES_THRESHOLD && !isHintUnlocked;
        const canBuy = hasTips && currentLifes <= PURCHASE_LIFES_THRESHOLD && currentLifes > FREE_LIFES_THRESHOLD && !isHintUnlocked;
        
        const showHintInModal = isHintUnlocked || !hasTips;

        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
                <div className={`p-6 rounded-xl shadow-2xl w-full max-w-sm bg-gray-900/90 border-4 border-yellow-500 transition-all duration-300 text-white`}>
                    <h2 className={`text-2xl font-bold mb-4 border-b pb-2 text-yellow-400`}>
                        {isHintUnlocked ? '💡 Indice Débloqué' : '🤔 Obtenir un Indice ?'}
                    </h2>

                    <p className="mb-4 text-sm font-mono text-gray-300">
                        {hintStatusMessage}
                    </p>

                    {showHintInModal && (
                        <div className="bg-yellow-900/50 p-3 rounded text-sm italic text-yellow-300 mb-4 whitespace-pre-wrap">
                            {actualHintContent}
                        </div>
                    )}
                    
                    <div className="flex justify-end space-x-3 mt-4">
                        <button 
                            onClick={() => setShowHintModal(false)}
                            className="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200"
                        >
                            Fermer
                        </button>
                        
                        {(canBuy || isFree) && !isHintUnlocked && (
                            <button
                                onClick={handleModalAction}
                                className={`font-bold py-2 px-4 rounded transition duration-200 ${isFree ? 'bg-green-700 hover:bg-green-800' : theme.buttonStyle}`}
                            >
                                {isFree ? "Recevoir GRATUITEMENT" : `Acheter (${XP_COST} XP)`}
                            </button>
                        )}
                    </div>
                </div>
            </div>
        );
    };

    // --- Composant d'Écran d'État (Victory / Game Over) ---
    const ScreenWrapper = ({ children, title, buttonText, buttonAction, imageSrc, isError = false }) => (
        <div 
            className="fixed inset-0 z-50 flex flex-col items-center justify-center p-4 bg-cover bg-center bg-fixed"
            style={{ backgroundImage: `url(${theme.background})`, backgroundSize: 'cover', backgroundRepeat: 'no-repeat' }}
        >
             <div className="absolute inset-0 bg-black/70"></div> 
            <div className={`relative p-8 lg:p-10 rounded-xl shadow-2xl w-full max-w-lg transition-all duration-300 transform scale-100 ${isError ? 'bg-red-900/90 border-4 border-red-500' : 'bg-green-900/90 border-4 border-green-500'} text-white text-center`}>
                <h1 className={`text-4xl lg:text-6xl font-extrabold mb-4 lg:mb-6 ${isError ? 'text-red-400 animate-pulse' : 'text-green-400'}`}>{title}</h1>
                
                {/* CORRECTION: Affichage conditionnel de l'image */}
                {imageSrc && ( 
                    <img 
                        src={imageSrc} 
                        alt={title} 
                        className="w-32 lg:w-48 mx-auto mb-6 object-contain transform transition-transform duration-500 hover:scale-105"
                    />
                )}

                {children}

                <button 
                    onClick={buttonAction} 
                    className={`mt-8 font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105 shadow-2xl ${isError ? 'bg-red-700 hover:bg-red-800' : 'bg-green-700 hover:bg-green-800'} text-lg`}
                >
                    {buttonText}
                </button>
            </div>
        </div>
    );

    // DÉCLENCHEURS DES POPUPS PLEIN ÉCRAN
    if (isGameOver) {
        return (
            <ScreenWrapper 
                title="GAME OVER" 
                buttonText="Recommencer le Niveau" 
                buttonAction={() => window.location.reload()}
                imageSrc={null} // CORRIGÉ: Pas d'image pour "GAME OVER"
                isError={true}
            >
                <p className="text-xl lg:text-2xl font-semibold">Le virus a contaminé le noyau.</p>
                <p className="text-lg mt-2">Veuillez relancer la séquence de purge.</p>
            </ScreenWrapper>
        );
    }

    if (isVictory) {
        return (
            <ScreenWrapper 
                title="VICTOIRE !" 
                buttonText="Niveau Suivant" 
                buttonAction={() => window.location.href = nextLevelPath} 
                imageSrc={null} // Pas d'image pour la victoire
                isError={false}
            >
                <p className="text-xl lg:text-2xl font-semibold mb-2">Code purgé avec succès !</p>
                <p className="text-lg text-yellow-300">XP Obtenue: <span className='font-bold'>{currentXpGain}</span> (Potentiel: {xpGain})</p>
                <p className="text-lg text-yellow-300">Vies restantes: <span className='font-bold'>{currentLifes}</span></p>
            </ScreenWrapper>
        );
    }


    // --- Rendu principal de l'interface de jeu ---

    return (
        <div 
            className="flex flex-col lg:flex-row w-full h-full min-h-full overflow-hidden p-2 lg:p-4 bg-cover bg-center transition-all duration-500" 
            style={{ backgroundImage: `url(${theme.background})` }}
        >
             <div className="inset-0 bg-black/50 lg:bg-black/40"></div>
             
             <div className="relative z-10 flex flex-col lg:flex-row w-full h-full space-y-4 lg:space-y-0 lg:space-x-4">
                {/* Colonne Gauche : Énoncé, Caractère, Vies */}
                <div className="w-full lg:w-1/3 flex flex-col space-y-4 max-h-1/2 lg:max-h-full">
                    
                    {/* Header : Vies, Langage, Indice (SÉPARÉ) */}
                    <div className="flex justify-between items-center p-3 rounded-lg bg-black/60 shadow-xl border-b-2 border-gray-700">
                        <h1 className={`text-2xl lg:text-3xl font-extrabold ${theme.uiTextColor}`}>{language.toUpperCase()}</h1>
                        
                        {/* 1. Affichage des Vies */}
                        {renderLifes()}
                        
                        {/* 2. Bouton Indice (unique) */}
                        <div className="flex items-center space-x-1">
                            <span className={`text-xl font-bold ${theme.uiTextColor} transition-colors duration-300`}>
                                {availableHints}
                            </span>
                            <button
                                onClick={handleHintButtonClick}
                                className={`text-2xl p-2 rounded-full transition duration-300 hover:scale-110 ${isHintUnlocked ? 'bg-yellow-600 animate-pulse' : 'bg-gray-700/80'} shadow-lg`}
                                title={`Indice (Coût: ${XP_COST} XP)`}
                            >
                                💡
                            </button>
                        </div>

                    </div>

                    {/* Énoncé et Cadre d'Astuce */}
                    <div className={`flex-grow overflow-y-auto ${theme.enonceStyle} shadow-2xl transition-all duration-300`}>
                        <h2 className={`text-2xl font-extrabold mb-2 border-b-2 pb-1 border-current`}>
                            <span className="mr-2">📝</span> Mission (Niveau {levelNumber}) :
                        </h2>
                        <pre className="whitespace-pre-wrap text-sm lg:text-base leading-relaxed break-words">{parsedEnonce}</pre>
                        
                        {/* Cadre d'Astuce */}
                        {isHintUnlocked && actualHintContent !== "Pas d'astuce disponible." && (
                            <div className={`mt-4 p-3 rounded-lg border-2 border-dashed border-yellow-700 bg-yellow-100/30 text-gray-900`}>
                                <h3 className="font-bold mb-1 flex items-center text-yellow-800"><span className="mr-2">💡</span> ASTUCE DÉBLOQUÉE :</h3>
                                <p className="text-sm italic whitespace-pre-wrap">{actualHintContent}</p>
                            </div>
                        )}
                        {!isHintUnlocked && (
                             <div className={`mt-4 p-3 rounded-lg border-2 border-dashed border-gray-500/50 text-gray-700/70`}>
                                <h3 className="font-bold mb-1 flex items-center"><span className="mr-2">💡</span> ASTUCE :</h3>
                                <p className="text-sm italic">Cliquer sur l'ampoule ci-dessus pour débloquer l'indice.</p>
                            </div>
                        )}
                    </div>
                    
                </div>

                {/* Colonne Droite : Éditeur & Console */}
                <div className="w-full lg:w-2/3 flex flex-col p-4 bg-black/80 rounded-lg shadow-2xl space-y-4 h-full">
                    <h2 className={`text-xl lg:text-2xl font-bold ${theme.uiTextColor} border-b border-gray-600 pb-1`}>
                        <span className="mr-2">💻</span> Éditeur de Code
                    </h2>
                    
                    {/* Éditeur CodeMirror */}
                    <div className="flex-1 overflow-hidden rounded-lg">
                        <CodeMirror
                            value={code}
                            height="100%"
                            minHeight="200px" 
                            theme={theme.codemirrorTheme} 
                            extensions={theme.extensions}
                            onChange={(value) => setCode(value)}
                            className="text-base"
                            options={{ tabSize: 4 }}
                        />
                    </div>

                    {/* Bouton de validation */}
                    <button
                        onClick={handleSubmit}
                        disabled={isSubmitting || !code.trim()}
                        className={`text-white font-extrabold py-3 px-6 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-95 ${theme.buttonStyle}`}
                    >
                        {isSubmitting ? '🕹️ Exécution...' : '✅ Valider le Code'}
                    </button>

                    {/* Console de Résultat */}
                    {submissionResult && (
                        <div className={`p-4 rounded-xl shadow-inner transition-colors duration-500 text-sm font-mono ${submissionResult.isSuccess ? 'bg-green-900/80 border-2 border-green-500' : 'bg-red-900/80 border-2 border-red-500'}`}>
                            <h3 className={`font-bold mb-2 text-lg ${submissionResult.isSuccess ? 'text-green-300' : 'text-red-300'} flex items-center`}>
                                {submissionResult.isSuccess ? '✅ TEST RÉUSSI' : '❌ TEST ÉCHOUÉ'}
                            </h3>
                            <div className="text-gray-300 space-y-1 max-h-32 overflow-y-auto pr-1">
                                {submissionResult.stderr && (
                                    <p className="text-red-400 font-bold">Erreur du compilateur: {submissionResult.stderr}</p>
                                )}
                                
                                {/* MASQUAGE DE LA SORTIE ATTENDUE SI ÉCHEC */}
                                {submissionResult.isSuccess && (
                                    <p>▶️ Sortie Attendu: <code className="bg-gray-700 p-0.5 rounded text-xs">{submissionResult.expectedOutput || 'N/A'}</code></p>
                                )}
                                
                                <p>◀️ Votre Sortie: <code className="bg-gray-700 p-0.5 rounded text-xs">{submissionResult.actualOutput || 'Pas de sortie'}</code></p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
            
            {showHintModal && <HintModal />}
            
        </div>
    );
};

export default GameInterface;