# 🔔 Notifications de Déploiement (Optionnel)

Ce fichier contient des exemples pour ajouter des notifications lors des déploiements.

## Discord

Ajoute un webhook Discord pour être notifié des déploiements.

### 1. Créer un Webhook Discord

1. Va sur ton serveur Discord
2. Paramètres du salon → Intégrations → Webhooks
3. Créer un webhook, copie l'URL
4. Ajoute le secret `DISCORD_WEBHOOK` dans GitHub

### 2. Ajouter au workflow

Ajoute ces steps dans `.github/workflows/deploy.yml` après le déploiement :

```yaml
      - name: 🔔 Discord Success Notification
        if: success()
        run: |
          curl -H "Content-Type: application/json" \
          -d '{
            "embeds": [{
              "title": "✅ Déploiement Réussi",
              "description": "Codyssey a été déployé avec succès !",
              "color": 3066993,
              "fields": [
                {
                  "name": "Branche",
                  "value": "${{ github.ref_name }}",
                  "inline": true
                },
                {
                  "name": "Commit",
                  "value": "${{ github.sha }}",
                  "inline": true
                },
                {
                  "name": "Auteur",
                  "value": "${{ github.actor }}",
                  "inline": true
                }
              ],
              "url": "${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}",
              "timestamp": "'$(date -u +%Y-%m-%dT%H:%M:%S.000Z)'"
            }]
          }' \
          ${{ secrets.DISCORD_WEBHOOK }}

      - name: 🔔 Discord Failure Notification
        if: failure()
        run: |
          curl -H "Content-Type: application/json" \
          -d '{
            "embeds": [{
              "title": "❌ Déploiement Échoué",
              "description": "Le déploiement de Codyssey a échoué !",
              "color": 15158332,
              "fields": [
                {
                  "name": "Branche",
                  "value": "${{ github.ref_name }}",
                  "inline": true
                },
                {
                  "name": "Auteur",
                  "value": "${{ github.actor }}",
                  "inline": true
                }
              ],
              "url": "${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}",
              "timestamp": "'$(date -u +%Y-%m-%dT%H:%M:%S.000Z)'"
            }]
          }' \
          ${{ secrets.DISCORD_WEBHOOK }}
```

## Slack

### 1. Créer un Webhook Slack

1. Va sur https://api.slack.com/apps
2. Créer une app → Incoming Webhooks → Activer
3. Ajouter à ton workspace et copie l'URL
4. Ajoute le secret `SLACK_WEBHOOK` dans GitHub

### 2. Ajouter au workflow

```yaml
      - name: 🔔 Slack Success Notification
        if: success()
        run: |
          curl -X POST ${{ secrets.SLACK_WEBHOOK }} \
          -H 'Content-Type: application/json' \
          -d '{
            "text": "✅ *Déploiement Réussi*",
            "blocks": [
              {
                "type": "section",
                "text": {
                  "type": "mrkdwn",
                  "text": "*Codyssey* a été déployé avec succès sur production !"
                }
              },
              {
                "type": "section",
                "fields": [
                  {
                    "type": "mrkdwn",
                    "text": "*Branche:*\n${{ github.ref_name }}"
                  },
                  {
                    "type": "mrkdwn",
                    "text": "*Auteur:*\n${{ github.actor }}"
                  }
                ]
              },
              {
                "type": "actions",
                "elements": [
                  {
                    "type": "button",
                    "text": {
                      "type": "plain_text",
                      "text": "Voir les logs"
                    },
                    "url": "${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}"
                  }
                ]
              }
            ]
          }'
```

## Email (GitHub native)

GitHub peut envoyer des emails automatiquement sur échec. Active dans :
- Settings → Notifications → Actions
- Coche "Send notifications for failed workflows only"

## Telegram

### 1. Créer un Bot Telegram

1. Parle à @BotFather sur Telegram
2. Crée un bot avec `/newbot`
3. Récupère le token
4. Récupère ton chat ID avec @userinfobot
5. Ajoute `TELEGRAM_BOT_TOKEN` et `TELEGRAM_CHAT_ID` dans GitHub

### 2. Ajouter au workflow

```yaml
      - name: 🔔 Telegram Notification
        if: always()
        run: |
          STATUS="${{ job.status }}"
          if [ "$STATUS" = "success" ]; then
            EMOJI="✅"
            MESSAGE="Déploiement réussi"
          else
            EMOJI="❌"
            MESSAGE="Déploiement échoué"
          fi
          
          curl -s -X POST https://api.telegram.org/bot${{ secrets.TELEGRAM_BOT_TOKEN }}/sendMessage \
          -d chat_id=${{ secrets.TELEGRAM_CHAT_ID }} \
          -d text="$EMOJI *Codyssey - $MESSAGE*%0A%0ABranche: ${{ github.ref_name }}%0AAuteur: ${{ github.actor }}" \
          -d parse_mode=Markdown
```

## Webhooks personnalisés

Tu peux aussi créer ton propre endpoint pour recevoir les notifications :

```yaml
      - name: 🔔 Custom Webhook
        if: always()
        run: |
          curl -X POST ${{ secrets.CUSTOM_WEBHOOK_URL }} \
          -H 'Content-Type: application/json' \
          -d '{
            "project": "codyssey",
            "status": "${{ job.status }}",
            "branch": "${{ github.ref_name }}",
            "commit": "${{ github.sha }}",
            "author": "${{ github.actor }}",
            "timestamp": "'$(date -u +%Y-%m-%dT%H:%M:%S.000Z)'",
            "logs_url": "${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}"
          }'
```

## Action GitHub prête à l'emploi

Tu peux aussi utiliser des actions GitHub existantes :

```yaml
      # Pour Slack
      - name: Slack Notification
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: Deployment completed
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
        if: always()
      
      # Pour Discord
      - name: Discord Notification
        uses: sarisia/actions-status-discord@v1
        if: always()
        with:
          webhook: ${{ secrets.DISCORD_WEBHOOK }}
          status: ${{ job.status }}
          title: "Codyssey Deployment"
          description: "Branch: ${{ github.ref_name }}"
```

## 💡 Conseils

- ⚠️ N'abuse pas des notifications (spam)
- 🎯 Notifie seulement les échecs si tu déploies souvent
- 📊 Utilise des couleurs/emojis pour différencier succès/échec
- 🔗 Ajoute toujours un lien vers les logs GitHub
