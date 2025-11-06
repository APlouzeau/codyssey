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
        character: `${IMAGE_BASE_PATH}Magi-Photoroom.jpg`, 
        extensions: [php()],
        languageName: 'php',
        codemirrorTheme: eclipse, 
        enonceStyle: 'bg-yellow-50/90 text-gray-900 border-4 border-amber-900/70 shadow-[0_0_20px_rgba(160,82,45,0.7)] p-4 lg:p-6 rounded-lg font-serif italic text-base scrollbar-thumb-amber-700 scrollbar-track-yellow-100 scrollbar-thin',
        codeContainerStyle: 'bg-gray-100/90 border-4 border-amber-900/70 shadow-2xl rounded-lg',
        uiTextColor: 'text-amber-900',
        buttonStyle: 'bg-amber-800 hover:bg-amber-900 text-yellow-100 font-extrabold shadow-md shadow-amber-900/50',
        victoryImage: `${IMAGE_BASE_PATH}Magigi-Photoroom.jpg`,
        tipCostColor: 'text-red-600',
    },
    javascript: {
        name: 'javascript',
        background: `${IMAGE_BASE_PATH}Capture_decran_2025-11-06_a_13.51.11.jpg`, 
        character: `${IMAGE_BASE_PATH}Gemini_Generated_Image_vyb1qgvyb1qgvyb1_09.40.48-Photoroom.jpg`, 
        extensions: [javascript({ jsx: true })],
        languageName: 'javascript',
        codemirrorTheme: dracula,
        enonceStyle: 'bg-gray-900/90 text-cyan-400 border-4 border-cyan-500 shadow-[0_0_25px_rgba(0,255,255,0.5)] p-4 lg:p-6 rounded-xl font-mono text-base scrollbar-thumb-cyan-500 scrollbar-track-gray-800 scrollbar-thin',
        codeContainerStyle: 'bg-gray-900/90 border-4 border-cyan-500 shadow-[0_0_20px_rgba(0,255,255,0.3)] rounded-xl',
        uiTextColor: 'text-cyan-400',
        buttonStyle: 'bg-cyan-600/70 hover:bg-cyan-500/80 border border-cyan-400 text-white shadow-[0_0_15px_rgba(0,255,255,0.7)]',
        victoryImage: `${IMAGE_BASE_PATH}victoire.png`,
        tipCostColor: 'text-red-400',
    },
    python: {
        name: 'python',
        background: `${IMAGE_BASE_PATH}1a6a54d6-80e0-4c19-9a21-3f060b1213e0.jpg`, 
        character: `${IMAGE_BASE_PATH}Firefly_Gemini_Flash_1-Photoroom.jpg`, 
        extensions: [python()],
        languageName: 'python',
        codemirrorTheme: dracula,
        enonceStyle: 'bg-green-900/85 text-lime-300 border-4 border-lime-500 shadow-[0_0_20px_rgba(50,205,50,0.7)] p-4 lg:p-6 rounded-lg font-sans text-base scrollbar-thumb-lime-500 scrollbar-track-green-800 scrollbar-thin',
        codeContainerStyle: 'bg-green-900/80 border-4 border-lime-500 shadow-xl rounded-lg',
        uiTextColor: 'text-lime-400',
        buttonStyle: 'bg-lime-700 hover:bg-lime-800 text-white shadow-[0_0_10px_rgba(100,205,50,0.5)]',
        victoryImage: `${IMAGE_BASE_PATH}Firefly_Gemini_Flash_1-Photoroom.jpg`,
        tipCostColor: 'text-red-400',
    },
};

// Fonction utilitaire pour décoder le JSON des props Twig
const parseJsonProp = (prop) => {
    if (typeof prop === 'string') {
        try {
            // Tente de parser, mais sécurise les props qui pourraient être null/empty
            const parsed = JSON.parse(prop);
            return parsed === null || parsed === undefined ? [] : parsed;
        } catch (e) {
            return []; // Retourne un tableau vide en cas d'erreur de parsing
        }
    }
    // Si ce n'est pas une chaîne ou si c'est null/undefined, assure un tableau si on s'attend à une collection
    return Array.isArray(prop) ? prop : [];
};

// --- 2. COMPOSANT PRINCIPAL ---

const GameInterface = ({ levelId, language, enonce, lifes, maxLifes, levelNumber, tips }) => {
    
    const [code, setCode] = useState('');
    const [currentLifes, setCurrentLifes] = useState(lifes);
    const [isGameOver, setIsGameOver] = useState(false);
    const [isVictory, setIsVictory] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submissionResult, setSubmissionResult] = useState(null); 
    const [showTipModal, setShowTipModal] = useState(false); 
    const [tipStatusMessage, setTipStatusMessage] = useState(''); 
    const [tipContent, setTipContent] = useState(null); 
    const [hasUsedFreeTip, setHasUsedFreeTip] = useState(false); 

    const parsedEnonce = useMemo(() => parseJsonProp(enonce), [enonce]);
    const normalizedLanguage = language.toLowerCase();
    
    const theme = THEMES[normalizedLanguage] || THEMES.javascript;
    const nextLevelPath = `/game/${normalizedLanguage}/${parseInt(levelNumber, 10) + 1}`; 
    
    // CORRECTION: Assurer que c'est un tableau, même si la sérialisation Doctrine échoue
    const parsedTips = useMemo(() => parseJsonProp(tips), [tips]); 
    const firstTip = parsedTips.length > 0 ? parsedTips[0].content : "Aucune astuce n'est disponible pour ce niveau.";
    
    const TIP_COST = 1;

    useEffect(() => {
        if (currentLifes <= 0 && !isVictory && !isGameOver) {
            setIsGameOver(true);
        }
    }, [currentLifes, isVictory, isGameOver]);

    useEffect(() => {
        setCurrentLifes(lifes);
    }, [lifes]);

    // Fonction pour gérer la logique de l'indice
    const handleTipClick = () => {
        
        // Si l'indice a déjà été acheté/donné, on l'affiche/ferme directement
        if (tipContent) {
            setShowTipModal(prev => !prev);
            return;
        }

        // Si l'indice est inexistant, on avertit.
        if (parsedTips.length === 0) {
            setTipStatusMessage("Il n'y a malheureusement aucune astuce liée à cet énoncé.");
            setShowTipModal(true);
            return;
        }
        
        // Logique de l'indice gratuit (3 vies restantes ou moins)
        if (currentLifes <= 3 && !hasUsedFreeTip) {
            setTipStatusMessage("Indice GRATUIT débloqué car il vous reste 3 vies ou moins !");
            setShowTipModal(true);
            return;
        }

        // Logique de l'achat d'indice (moins de la moitié des vies)
        if (currentLifes <= maxLifes / 2) {
            // Laissez l'utilisateur acheter l'indice via le modal
            setTipStatusMessage(`Vous pouvez acheter l'indice pour ${TIP_COST} vie.`);
            setShowTipModal(true);
        } else {
            // Bloquer l'achat (plus de la moitié des vies restantes)
            setTipStatusMessage(`Vous devez descendre en dessous de ${maxLifes / 2} vies (${Math.floor(maxLifes / 2) + 1} ou moins) pour acheter un indice.`);
            setShowTipModal(true); 
        }
    };
    
    // Fonction d'achat effective
    const buyTip = () => {
        if (tipContent) {
            setShowTipModal(false);
            return;
        }

        // L'indice gratuit est débloqué
        const isFree = currentLifes <= 3 && !hasUsedFreeTip;
        
        if (isFree) {
            setTipContent(firstTip);
            setHasUsedFreeTip(true); 
            setTipStatusMessage("Indice GRATUIT activé !");
            return;
        }
        
        // L'indice est payant
        if (currentLifes <= maxLifes / 2 && currentLifes > TIP_COST) {
            // Achat confirmé
            // NOTE: Vous devrez mettre à jour les vies dans la BDD dans un vrai jeu. 
            // Ici, nous ne faisons que la mise à jour côté client pour l'affichage immédiat.
            setCurrentLifes(currentLifes - TIP_COST);
            setTipContent(firstTip);
            setTipStatusMessage("Indice acheté ! Bonne chance. La vie a été déduite.");
        } else if (currentLifes <= TIP_COST) {
             setTipStatusMessage("Achat impossible : vous n'avez pas assez de vies !");
        } else if (currentLifes > maxLifes / 2) {
             setTipStatusMessage("Achat impossible : vous devez descendre en dessous de la moitié des vies.");
        }
        // Le modal reste ouvert pour afficher le résultat de l'achat/blocage
    };

    // --- Logique de Soumission (Inchngée) ---
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
                }),
            });

            const result = await response.json();
            setSubmissionResult(result);

            if (result.newLifes !== undefined) {
                setCurrentLifes(result.newLifes);
            }
            
            if (result.isSuccess) {
                setIsVictory(true);
            }
            
        } catch (error) {
            console.error('Erreur lors de la soumission du code:', error);
            setSubmissionResult({ isSuccess: false, error: 'Erreur de connexion ou API.' });
        } finally {
            setIsSubmitting(false);
        }
    }, [code, theme.languageName, levelId, currentLifes, isSubmitting, isGameOver, isVictory]);

    // --- Rendu des Vies (Inchngée) ---
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
                {currentLifes} / {maxLifes}
            </span>
        </div>
    );

    // --- Composant Modal d'Indice ---
    const TipModal = () => {
        // Logique d'affichage des boutons
        const hasTips = parsedTips.length > 0;
        const isFree = hasTips && currentLifes <= 3 && !tipContent;
        const canBuy = hasTips && currentLifes <= maxLifes / 2 && currentLifes > TIP_COST && !tipContent;
        
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
                <div className={`p-6 rounded-xl shadow-2xl w-full max-w-sm ${theme.codeContainerStyle} border ${theme.uiTextColor} transition-all duration-300`}>
                    <h2 className={`text-2xl font-bold mb-4 border-b pb-2 ${theme.uiTextColor}`}>
                        {tipContent ? 'Indice Débloqué !' : '💡 Gestion de l\'Indice'}
                    </h2>

                    {/* Contenu ou Message d'état */}
                    <p className="text-white mb-4 text-sm font-mono">
                        {tipContent || tipStatusMessage || "Voulez-vous acheter un indice ?"}
                    </p>

                    {/* Affichage de l'indice déjà débloqué */}
                    {tipContent && (
                        <div className="bg-white/10 p-3 rounded text-sm italic text-yellow-300 mb-4 whitespace-pre-wrap">
                            {tipContent}
                        </div>
                    )}
                    
                    {/* Boutons d'action */}
                    <div className="flex justify-end space-x-3 mt-4">
                        <button 
                            onClick={() => setShowTipModal(false)}
                            className="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200"
                        >
                            Fermer
                        </button>
                        
                        {/* Bouton d'achat/gratuit si disponible */}
                        {(canBuy || isFree) && !tipContent && (
                            <button
                                onClick={buyTip}
                                className={`font-bold py-2 px-4 rounded transition duration-200 ${isFree ? 'bg-green-700 hover:bg-green-800 text-white shadow-lg' : theme.buttonStyle}`}
                            >
                                {isFree ? "Recevoir GRATUITEMENT" : `Acheter (1 Vie)`}
                            </button>
                        )}
                    </div>
                </div>
            </div>
        );
    };


    // --- Rendu principal ---

    return (
        <div 
            className="flex flex-col lg:flex-row h-screen w-full overflow-hidden p-2 lg:p-4 bg-cover bg-center bg-fixed transition-all duration-500" 
            style={{ backgroundImage: `url(${theme.background})` }}
        >
             <div className="absolute inset-0 bg-black/50 lg:bg-black/40"></div>
             
             <div className="relative z-10 flex flex-col lg:flex-row w-full h-full space-y-4 lg:space-y-0 lg:space-x-4">
                {/* Colonne Gauche : Énoncé, Caractère, Vies */}
                <div className="w-full lg:w-1/3 flex flex-col space-y-4 max-h-1/2 lg:max-h-full">
                    
                    {/* Header : Vies, Langage, Astuce */}
                    <div className="flex justify-between items-center p-3 rounded-lg bg-black/60 shadow-xl border-b-2 border-gray-700">
                        <h1 className={`text-2xl lg:text-3xl font-extrabold ${theme.uiTextColor}`}>{language.toUpperCase()}</h1>
                        <div className="flex items-center space-x-3">
                            {renderLifes()}
                            {/* Bouton Ampoule (Déclenche la nouvelle logique) */}
                            <button
                                onClick={handleTipClick}
                                className={`text-2xl p-2 rounded-full transition duration-300 hover:scale-110 ${tipContent ? 'bg-yellow-600 animate-pulse' : 'bg-gray-700/80'} shadow-lg`}
                                title="Obtenir un indice"
                            >
                                💡
                            </button>
                        </div>
                    </div>

                    {/* Énoncé */}
                    <div className={`flex-grow overflow-y-auto ${theme.enonceStyle} shadow-2xl transition-all duration-300`}>
                        <h2 className={`text-2xl font-extrabold mb-2 border-b-2 pb-1 border-current`}>
                            <span className="mr-2">📝</span> Mission (Niveau {levelNumber}) :
                        </h2>
                        <pre className="whitespace-pre-wrap text-sm lg:text-base leading-relaxed break-words">{parsedEnonce}</pre>
                    </div>
                    
                    {/* Personnage */}
                    <div className="hidden lg:block self-center p-2 mt-auto">
                        <img 
                            src={`${theme.character}`} 
                            alt={`${theme.name} character`} 
                            className="w-40 h-auto object-contain drop-shadow-2xl"
                        />
                    </div>
                </div>

                {/* Colonne Droite : Éditeur & Console */}
                <div className="w-full lg:w-2/3 flex flex-col p-4 bg-black/80 rounded-lg shadow-2xl space-y-4 h-full">
                    <h2 className={`text-xl lg:text-2xl font-bold ${theme.uiTextColor} border-b border-gray-600 pb-1`}>
                        <span className="mr-2">💻</span> Éditeur de Code
                    </h2>
                    
                    {/* Éditeur CodeMirror */}
                    <div className={`flex-1 overflow-hidden rounded-lg ${theme.codeContainerStyle}`}>
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

                    {/* Console de Résultat (RÉINTÉGRÉE) */}
                    {submissionResult && (
                        <div className={`p-4 rounded-xl shadow-inner transition-colors duration-500 text-sm font-mono ${submissionResult.isSuccess ? 'bg-green-900/80 border-2 border-green-500' : 'bg-red-900/80 border-2 border-red-500'}`}>
                            <h3 className={`font-bold mb-2 text-lg ${submissionResult.isSuccess ? 'text-green-300' : 'text-red-300'} flex items-center`}>
                                {submissionResult.isSuccess ? '✅ TEST RÉUSSI' : '❌ TEST ÉCHOUÉ'}
                            </h3>
                            <div className="text-gray-300 space-y-1 max-h-32 overflow-y-auto pr-1">
                                {submissionResult.stderr && (
                                    <p className="text-red-400 font-bold">Erreur du compilateur: {submissionResult.stderr}</p>
                                )}
                                <p>▶️ Sortie Attendu: <code className="bg-gray-700 p-0.5 rounded text-xs">{submissionResult.expectedOutput || 'N/A'}</code></p>
                                <p>◀️ Votre Sortie: <code className="bg-gray-700 p-0.5 rounded text-xs">{submissionResult.actualOutput || 'Pas de sortie'}</code></p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
            
            {/* Rendu du Modal d'Indice (s'affiche au clic sur l'ampoule) */}
            {showTipModal && <TipModal />}
        </div>
    );
};

export default GameInterface;