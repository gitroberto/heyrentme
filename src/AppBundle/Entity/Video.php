<?php
namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Video {

    const TYPE_VIMEO = 1;
    const TYPE_YOUTUBE = 2;
    
    const RE_VIMEO = '~
		# Match Vimeo link and embed code
		(?:<iframe [^>]*src=")?         # If iframe match up to first quote of src
		(?:                             # Group vimeo url
				https?:\/\/             # Either http or https
				(?:[\w]+\.)*            # Optional subdomains
				vimeo\.com              # Match vimeo.com
				(?:[\/\w]*\/videos?)?   # Optional video sub directory this handles groups links also
				\/                      # Slash before Id
				([0-9]+)                # $1: VIDEO_ID is numeric
				[^\s]*                  # Not a space
		)                               # End group
		"?                              # Match end quote if part of src
		(?:[^>]*></iframe>)?            # Match the end of the iframe
		(?:<p>.*</p>)?                  # Match any title information stuff
		~ix';   // source: https://gist.github.com/wwdboer/4943672    
    const RE_YOUTUBE = '/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/ix';   
    
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    protected $type;
    /**
     * @ORM\Column(type="string")
     */
    protected $videoId;
    /**
     * @ORM\Column(type="string")
     */
    protected $embedUrl;
    /**
     * @ORM\Column(type="string")
     */
    protected $originalUrl;
    /**
     * @ORM\Column(type="string")
     */
    protected $thumbnailUrl;
    /**
     * @ORM\Column(type="string")
     */
    protected $createdAt;
    /**
     * @ORM\Column(type="string")
     */
    protected $modifiedAt;    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Video
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set videoId
     *
     * @param string $videoId
     *
     * @return Video
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Get videoId
     *
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Set embedUrl
     *
     * @param string $embedUrl
     *
     * @return Video
     */
    public function setEmbedUrl($embedUrl)
    {
        $this->embedUrl = $embedUrl;

        return $this;
    }

    /**
     * Get embedUrl
     *
     * @return string
     */
    public function getEmbedUrl()
    {
        return $this->embedUrl;
    }

    /**
     * Set originalUrl
     *
     * @param string $originalUrl
     *
     * @return Video
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    /**
     * Get originalUrl
     *
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * Set thumbnailUrl
     *
     * @param string $thumbnailUrl
     *
     * @return Video
     */
    public function setThumbnailUrl($thumbnailUrl)
    {
        $this->thumbnailUrl = $thumbnailUrl;

        return $this;
    }

    /**
     * Get thumbnailUrl
     *
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }

    /**
     * Set createdAt
     *
     * @param string $createdAt
     *
     * @return Video
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param string $modifiedAt
     *
     * @return Video
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }
}
