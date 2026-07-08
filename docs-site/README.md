# LoveGem Framework Documentation

This folder contains the documentation site for LoveGem Framework.

## GitHub Pages

The documentation is automatically deployed to GitHub Pages when changes are pushed to the `main` branch.

### Setup Instructions

1. Go to your repository Settings
2. Navigate to Pages
3. Source: Deploy from a branch
4. Branch: `main`
5. Folder: `/docs-site`
6. Click Save

### Access Documentation

Once deployed, your documentation will be available at:
```
https://gamingwithashis07-sys.github.io/Codeble-Environment-/
```

## Local Development

To preview documentation locally:

```bash
# Using Python
cd docs-site
python -m http.server 8000

# Using PHP
cd docs-site
php -S localhost:8000
```

Then visit: http://localhost:8000

## Files

- `index.html` - Main documentation page
- `_config.yml` - Jekyll configuration (for GitHub Pages)

## Customization

To customize the documentation:
1. Edit `index.html`
2. Update styles in the `<style>` section
3. Add new sections as needed
4. Push changes to GitHub

## License

MIT License - LoveGem Framework
