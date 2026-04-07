# =============================================================================
# PROJET ML : PRÉDICTION DE CHURN BANCAIRE
# Dataset   : Churn_Modelling.csv
# Source    : https://www.kaggle.com/datasets/shrutimechlearn/churn-modelling
# Objectif  : Prédire si un client va quitter la banque (Exited = 1)
# =============================================================================

# ─────────────────────────────────────────────
# ÉTAPE 1 — IMPORTS
# ─────────────────────────────────────────────
import pandas as pd                              # Manipulation de données tabulaires
import numpy as np                               # Calcul numérique
import matplotlib.pyplot as plt                  # Graphiques de base
import seaborn as sns                            # Graphiques statistiques (heatmaps, etc.)

from sklearn.model_selection import train_test_split   # Découpage train/test
from sklearn.preprocessing import StandardScaler       # Normalisation des features
from sklearn.preprocessing import LabelEncoder         # Encodage binaire (Male/Female → 0/1)
from sklearn.linear_model import LogisticRegression    # Modèle 1 : Régression logistique
from sklearn.ensemble import RandomForestClassifier    # Modèle 2 : Forêt aléatoire

from sklearn.metrics import (
    classification_report,   # Precision, Recall, F1 par classe
    confusion_matrix,         # Matrice de confusion
    roc_auc_score,            # Score AUC (aire sous la courbe ROC)
    roc_curve                 # Points (FPR, TPR) pour tracer la courbe ROC
)

import warnings
warnings.filterwarnings('ignore')   # Masquer les warnings non critiques

# ─────────────────────────────────────────────
# ÉTAPE 2 — CHARGEMENT DES DONNÉES
# ─────────────────────────────────────────────
df = pd.read_csv('Churn_Modelling.csv')

print("=" * 60)
print("APERÇU DU DATASET")
print("=" * 60)
print(f"Dimensions : {df.shape[0]} lignes × {df.shape[1]} colonnes\n")
print(df.head())
print("\nTypes de colonnes :")
print(df.dtypes)
print("\nValeurs manquantes :")
print(df.isnull().sum())

# ─────────────────────────────────────────────
# ÉTAPE 3 — ANALYSE EXPLORATOIRE (EDA)
# ─────────────────────────────────────────────
print("\n" + "=" * 60)
print("ANALYSE DE LA VARIABLE CIBLE")
print("=" * 60)

churn_counts = df['Exited'].value_counts()
churn_pct    = df['Exited'].value_counts(normalize=True) * 100

print(f"Clients restants  (0) : {churn_counts[0]:>5}  ({churn_pct[0]:.1f}%)")
print(f"Clients partis    (1) : {churn_counts[1]:>5}  ({churn_pct[1]:.1f}%)")

# Visualisation 1 : Distribution de la variable cible
fig, axes = plt.subplots(1, 2, figsize=(12, 5))

axes[0].bar(['Resté (0)', 'Parti (1)'], churn_counts.values,
            color=['#2196F3', '#F44336'])
axes[0].set_title("Distribution du Churn")
axes[0].set_ylabel("Nombre de clients")

# Visualisation 2 : Âge vs Churn
axes[1].hist(df[df['Exited'] == 0]['Age'], bins=30, alpha=0.6,
             color='#2196F3', label='Resté')
axes[1].hist(df[df['Exited'] == 1]['Age'], bins=30, alpha=0.6,
             color='#F44336', label='Parti')
axes[1].set_title("Distribution de l'Âge selon le Churn")
axes[1].set_xlabel("Âge")
axes[1].set_ylabel("Fréquence")
axes[1].legend()

plt.tight_layout()
plt.savefig('eda_distribution.png', dpi=150, bbox_inches='tight')
plt.show()
print("→ Graphique sauvegardé : eda_distribution.png")

# Visualisation 3 : Corrélation entre variables numériques
plt.figure(figsize=(10, 7))
numeric_cols = df.select_dtypes(include=[np.number]).columns.tolist()
corr_matrix  = df[numeric_cols].corr()
sns.heatmap(corr_matrix, annot=True, fmt='.2f', cmap='coolwarm',
            center=0, square=True, linewidths=0.5)
plt.title("Matrice de Corrélation")
plt.tight_layout()
plt.savefig('correlation_matrix.png', dpi=150, bbox_inches='tight')
plt.show()
print("→ Graphique sauvegardé : correlation_matrix.png")

# ─────────────────────────────────────────────
# ÉTAPE 4 — PREPROCESSING (PRÉPARATION DES DONNÉES)
# ─────────────────────────────────────────────

# 4a. Suppression des colonnes inutiles pour le modèle
#     RowNumber  → simple numéro d'index, aucune valeur prédictive
#     CustomerId → identifiant unique, ne prédit rien
#     Surname    → nom de famille, trop spécifique (risque de surapprentissage)
df = df.drop(columns=['RowNumber', 'CustomerId', 'Surname'])

# 4b. Encodage de la colonne "Gender" (variable binaire)
#     LabelEncoder transforme : Female → 0, Male → 1
le = LabelEncoder()
df['Gender'] = le.fit_transform(df['Gender'])

# 4c. One-Hot Encoding de la colonne "Geography" (variable multi-catégories)
#     France / Germany / Spain → 3 catégories → 2 colonnes booléennes
#     (drop_first=True supprime une colonne pour éviter la multicolinéarité)
#     Résultat : Geography_Germany, Geography_Spain
#                Si les deux sont False → c'est France (référence)
df = pd.get_dummies(df, columns=['Geography'], drop_first=True)

print("\n" + "=" * 60)
print("DATASET APRÈS PREPROCESSING")
print("=" * 60)
print(df.head())
print(f"\nColonnes : {list(df.columns)}")

# ─────────────────────────────────────────────
# ÉTAPE 5 — SÉPARATION FEATURES / TARGET
# ─────────────────────────────────────────────

# X  : toutes les colonnes SAUF la colonne cible (les "entrées" du modèle)
# y  : uniquement la colonne cible "Exited" (ce qu'on veut prédire)
X = df.drop(columns=['Exited'])
y = df['Exited']

print("\n" + "=" * 60)
print("SÉPARATION X / y")
print("=" * 60)
print(f"X (features) : {X.shape}  →  {list(X.columns)}")
print(f"y (target)   : {y.shape}  →  valeurs possibles : {y.unique()}")

# ─────────────────────────────────────────────
# ÉTAPE 6 — TRAIN / TEST SPLIT
# ─────────────────────────────────────────────

# test_size=0.2    → 20% des données pour le test, 80% pour l'entraînement
# random_state=42  → graine aléatoire : résultats identiques à chaque exécution
# stratify=y       → garantit que la proportion de churn (20%) est
#                    préservée dans le train ET dans le test
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print("\n" + "=" * 60)
print("TRAIN / TEST SPLIT")
print("=" * 60)
print(f"X_train : {X_train.shape}  |  X_test : {X_test.shape}")
print(f"Churn dans train : {y_train.mean():.2%}")
print(f"Churn dans test  : {y_test.mean():.2%}")

# ─────────────────────────────────────────────
# ÉTAPE 7 — NORMALISATION (StandardScaler)
# ─────────────────────────────────────────────

# La régression logistique est sensible aux échelles différentes
# (ex: Age entre 18-95, Balance entre 0-250 000 → déséquilibre)
# StandardScaler centre chaque feature sur 0 (moyenne) et réduit l'écart-type à 1

# RÈGLE CRITIQUE : on "fit" UNIQUEMENT sur X_train, jamais sur X_test
# Pourquoi ? Parce que le scaler doit apprendre la moyenne/écart-type
# uniquement depuis les données d'entraînement, sinon on "triche"
# en laissant entrer de l'information du test dans le modèle.

scaler       = StandardScaler()
X_train_sc   = scaler.fit_transform(X_train)   # Apprend les stats ET transforme
X_test_sc    = scaler.transform(X_test)         # Applique les mêmes stats, sans réapprendre

# ─────────────────────────────────────────────
# ÉTAPE 8 — MODÈLE 1 : RÉGRESSION LOGISTIQUE
# ─────────────────────────────────────────────

lr_model = LogisticRegression(
    random_state=42,    # Reproductibilité
    max_iter=1000       # Nombre max d'itérations pour converger
)

lr_model.fit(X_train_sc, y_train)   # Entraînement

y_pred_lr    = lr_model.predict(X_test_sc)               # Prédictions binaires (0 ou 1)
y_proba_lr   = lr_model.predict_proba(X_test_sc)[:, 1]   # Probabilité d'être churn (classe 1)

# ─────────────────────────────────────────────
# ÉTAPE 9 — MODÈLE 2 : RANDOM FOREST
# ─────────────────────────────────────────────

rf_model = RandomForestClassifier(
    n_estimators=100,   # Nombre d'arbres de décision dans la forêt
    random_state=42,    # Reproductibilité
    n_jobs=-1           # Utilise tous les cœurs CPU disponibles
)

# Random Forest n'a pas besoin de normalisation (il travaille avec des seuils)
rf_model.fit(X_train, y_train)

y_pred_rf    = rf_model.predict(X_test)
y_proba_rf   = rf_model.predict_proba(X_test)[:, 1]

# ─────────────────────────────────────────────
# ÉTAPE 10 — ÉVALUATION DES MODÈLES
# ─────────────────────────────────────────────

print("\n" + "=" * 60)
print("ÉVALUATION — RÉGRESSION LOGISTIQUE")
print("=" * 60)
print(classification_report(y_test, y_pred_lr,
                             target_names=['Resté (0)', 'Parti (1)']))
print(f"ROC-AUC Score : {roc_auc_score(y_test, y_proba_lr):.4f}")

print("\n" + "=" * 60)
print("ÉVALUATION — RANDOM FOREST")
print("=" * 60)
print(classification_report(y_test, y_pred_rf,
                             target_names=['Resté (0)', 'Parti (1)']))
print(f"ROC-AUC Score : {roc_auc_score(y_test, y_proba_rf):.4f}")

# ─────────────────────────────────────────────
# ÉTAPE 11 — VISUALISATION : MATRICES DE CONFUSION
# ─────────────────────────────────────────────

fig, axes = plt.subplots(1, 2, figsize=(12, 5))

modeles  = [('Régression Logistique', y_pred_lr), ('Random Forest', y_pred_rf)]
couleurs = ['Blues', 'Greens']

for ax, (titre, preds), cmap in zip(axes, modeles, couleurs):
    cm = confusion_matrix(y_test, preds)
    sns.heatmap(cm, annot=True, fmt='d', cmap=cmap, ax=ax,
                xticklabels=['Prédit : Resté', 'Prédit : Parti'],
                yticklabels=['Réel : Resté', 'Réel : Parti'])
    ax.set_title(f'Matrice de Confusion\n{titre}')
    ax.set_xlabel('Valeur prédite')
    ax.set_ylabel('Valeur réelle')

plt.tight_layout()
plt.savefig('confusion_matrices.png', dpi=150, bbox_inches='tight')
plt.show()
print("→ Graphique sauvegardé : confusion_matrices.png")

# ─────────────────────────────────────────────
# ÉTAPE 12 — VISUALISATION : COURBE ROC
# ─────────────────────────────────────────────

plt.figure(figsize=(8, 6))

for nom, y_proba in [('Régression Logistique', y_proba_lr),
                      ('Random Forest', y_proba_rf)]:
    fpr, tpr, _ = roc_curve(y_test, y_proba)
    auc_score    = roc_auc_score(y_test, y_proba)
    plt.plot(fpr, tpr, linewidth=2, label=f'{nom}  (AUC = {auc_score:.3f})')

# Ligne de référence : un modèle aléatoire aurait une AUC de 0.5
plt.plot([0, 1], [0, 1], 'k--', linewidth=1, label='Aléatoire (AUC = 0.500)')

plt.xlabel('Taux de Faux Positifs (FPR)')
plt.ylabel('Taux de Vrais Positifs (TPR / Recall)')
plt.title('Courbe ROC — Comparaison des modèles')
plt.legend(loc='lower right')
plt.grid(True, alpha=0.3)
plt.tight_layout()
plt.savefig('roc_curve.png', dpi=150, bbox_inches='tight')
plt.show()
print("→ Graphique sauvegardé : roc_curve.png")

# ─────────────────────────────────────────────
# ÉTAPE 13 — FEATURE IMPORTANCE (RANDOM FOREST)
# ─────────────────────────────────────────────

# Le Random Forest peut dire quelles features ont le plus contribué
# aux décisions de ses arbres → très utile pour comprendre le "pourquoi"

importances = pd.Series(rf_model.feature_importances_, index=X.columns)
importances = importances.sort_values(ascending=True)  # Tri croissant pour barh

plt.figure(figsize=(10, 6))
colors = ['#F44336' if imp > importances.median() else '#90CAF9'
          for imp in importances.values]
importances.plot(kind='barh', color=colors)
plt.axvline(x=importances.median(), color='black', linestyle='--',
            alpha=0.5, label=f'Médiane ({importances.median():.3f})')
plt.title("Importance des Features — Random Forest")
plt.xlabel("Importance (Gini)")
plt.legend()
plt.tight_layout()
plt.savefig('feature_importance.png', dpi=150, bbox_inches='tight')
plt.show()
print("→ Graphique sauvegardé : feature_importance.png")

# ─────────────────────────────────────────────
# RÉCAPITULATIF FINAL
# ─────────────────────────────────────────────
print("\n" + "=" * 60)
print("RÉCAPITULATIF")
print("=" * 60)
print(f"{'Métrique':<25} {'Rég. Logistique':>20} {'Random Forest':>15}")
print("-" * 60)

from sklearn.metrics import accuracy_score
acc_lr = accuracy_score(y_test, y_pred_lr)
acc_rf = accuracy_score(y_test, y_pred_rf)
auc_lr = roc_auc_score(y_test, y_proba_lr)
auc_rf = roc_auc_score(y_test, y_proba_rf)

print(f"{'Accuracy':<25} {acc_lr:>20.4f} {acc_rf:>15.4f}")
print(f"{'ROC-AUC':<25} {auc_lr:>20.4f} {auc_rf:>15.4f}")
print("=" * 60)
print("✓ Projet terminé. Fichiers générés : 4 graphiques PNG")
