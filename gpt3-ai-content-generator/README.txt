=== AI Power: Complete AI Pack ===
Contributors: senols
Tags: ai, chatbot, openai, gpt, chatgpt
Requires at least: 5.0.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 2.3.13
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A complete AI toolkit for WordPress. Includes a content writer, chatbot, image generator, AI forms, automation, AI training, and more.

== Description ==

AI Power is a comprehensive set of artificial intelligence tools that works directly inside your WordPress dashboard. It is a collection of interconnected modules designed to help you with content creation, site management, and user interaction.

This plugin operates on a "Bring Your Own API Key" model. You need to have an API key from your preferred AI provider (like OpenAI, Google, etc.) to use the features.

**Key Features:**

*   **AI Chatbot**: Build custom chatbots and deploy them anywhere on your site using a shortcode or as a popup. Train the chatbot on your own content, enable web search, set usage limits, and create automated triggers for advanced interactions. Supports voice input and playback.
*   **Content Writer**: Generate high-quality articles, product descriptions, or any other text content. Input ideas from a list, CSV file, RSS feeds, or a list of URLs.
*   **AI Forms**: A drag-and-drop form builder that uses AI to process user input. Create custom tools that can generate anything from a blog post outline to a customer support reply based on what your users enter.
*   **Automation Engine**: Schedule AI tasks to run in the background. Automate content creation, update existing posts, index your content into a knowledge base, and automatically reply to blog comments.
*   **Image Generator**: Add a text-to-image generator to your site using a shortcode. Supports OpenAI (DALL-E 3, GPT-4o), Google (Imagen), and Replicate models, as well as free stock photos from Pexels and Pixabay.
*   **AI Training**: Create a custom knowledge base by "training" the AI on your own content. You can upload text, files, or index existing WordPress posts, pages, and products. This knowledge base can be used by chatbots and AI forms to provide answers based on your data. Supports OpenAI Vector Stores, Pinecone, and Qdrant.
*   **WooCommerce Tools**: Generate or enhance product descriptions, titles, and short descriptions using AI. You can also sell token packages to your users to monetize access to the AI features on your site.
*   **Content Assistant**: A suite of tools to improve your existing content. Bulk-enhance posts, generate new titles and excerpts from the posts list, or process selected text directly within the Classic and Block editors.
*   **REST API**: Programmatically access the plugin's core functionalities (text generation, image generation, embeddings, chat) from external applications.
*   **Flexible AI Providers**: Connect to multiple AI services. The plugin supports OpenAI, Google, Microsoft Azure, OpenRouter, and DeepSeek.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/gpt3-ai-content-generator` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Navigate to the 'AIP' menu in your WordPress dashboard.
4.  Go to the 'Dashboard' tab and enter your API key for at least one AI provider (e.g., OpenAI).
5.  Click the 'Sync' button next to the model selection to fetch available models.
6.  Explore the different modules (Chat, Write, Automate, etc.) from the main navigation to start using the tools.

== Frequently Asked Questions ==

= Do I need to buy credits or an API key from you? =

AI Power works with your own API key from your preferred AI provider (like OpenAI, Google, etc.). You are responsible for all costs associated with your usage of those third-party APIs. As long as you have enough api credits from those provider, you can use the plugin.

= What AI providers are supported? =

The plugin supports multiple AI providers. You can configure API keys for OpenAI, Google, Microsoft Azure, OpenRouter, and DeepSeek in the main dashboard.

= How do I train the AI on my own content? =

You can create a custom "knowledge base" using the **Train** module. There, you can upload text, PDF files, or select existing WordPress posts, pages, or products to be indexed into a vector store (provided by OpenAI, Pinecone, or Qdrant). Once your knowledge base is created, you can connect it to a Chatbot or an AI Form in their respective settings under the "Context" section. This allows the AI to use your data to provide more relevant responses.

= Can I control how much users can use the AI features? =

Yes. The **Token Management** add-on allows you to set usage limits. For each module (Chat, AI Forms, Image Generator), you can configure limits for guests and for logged-in users based on their WordPress role (e.g., Subscriber, Customer). You can set these limits to reset daily, weekly, monthly, or never.

= Can I sell access to the AI tools on my site? =

Yes. Using the **User Credits** module and our WooCommerce integration, you can create and sell "token packages". When a user purchases a package, the tokens are added to their account balance. This balance is used before their free periodic limits, allowing you to monetize AI features on your website.

== Screenshots ==

1. The main dashboard where you configure AI providers and general settings.
2. The Content Writer interface for generating single or bulk articles.
3. The Chatbot builder, showing the various configuration accordions.
4. The AI Forms module, with the drag-and-drop form builder.
5. The AI Training module, for managing your custom knowledge bases.
6. The Automate module, showing the task list and queue.

== Changelog ==

= 2.3.13 =

This is a major update.

While a Migration Tool is available, I strongly recommend **not migrating old data** and instead starting with a fresh setup.

The new version includes powerful features like **OpenAI Vector Store** support, which is faster and easier to manage.

If you were previously using external services like **Pinecone** or **Qdrant**, you might not need them anymore.

The new built-in OpenAI Vector Store is optimized for performance and fully integrated with AI Power 2.3+.

If you still need to migrate old data please check the [migration tool](https://docs.aipower.org/docs/getting-started/migration-from-legacy)

Please check our [documentation](https://docs.aipower.org/) for the new features.